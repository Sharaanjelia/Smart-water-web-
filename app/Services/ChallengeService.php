<?php

namespace App\Services;

use App\Models\Challenge;
use App\Models\ChallengeMission;
use App\Models\ChallengeParticipant;
use App\Models\ChallengeSubmission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ChallengeService
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Dapatkan daftar program tantangan aktif beserta progres peserta.
     */
    public function getActiveChallenges(?User $user): array
    {
        $challenges = Challenge::with('missions')->where('is_active', true)->get();
        $progress = $this->getUserProgress($user);

        return [
            'challenges' => $challenges,
            'progress'   => $progress,
        ];
    }

    /**
     * Ambil data progres tantangan pengguna dari database MySQL dan fallback session.
     */
    public function getUserProgress(?User $user): array
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }

        $sessionProgress = $_SESSION['swc_challenge_progress'] ?? [
            'activeChallenges'    => ['challenge-1'],
            'completedMissions'   => ['m-1-1'],
            'points'              => 100,
            'completedChallenges' => [],
        ];

        if (!$user) {
            return $sessionProgress;
        }

        // Ambil data tantangan yang diikuti dari database
        $participants = ChallengeParticipant::where('user_id', $user->id)
            ->with(['challenge', 'submissions.mission'])
            ->get();

        $activeChallenges = [];
        $completedChallenges = [];
        $completedMissions = [];

        foreach ($participants as $part) {
            $code = $part->challenge?->challenge_code ?? (string)$part->challenge_id;
            if ($part->status === 'COMPLETED') {
                $completedChallenges[] = $code;
            } else {
                $activeChallenges[] = $code;
            }

            foreach ($part->submissions as $sub) {
                if ($sub->is_completed && $sub->mission) {
                    $completedMissions[] = $sub->mission->mission_code;
                }
            }
        }

        $profile = $user->customerProfile;
        $points = $profile?->points ?? (int)($sessionProgress['points'] ?? 100);

        $mergedActive = array_values(array_unique(array_merge(
            $sessionProgress['activeChallenges'] ?? [],
            $activeChallenges
        )));

        $mergedCompleted = array_values(array_unique(array_merge(
            $sessionProgress['completedChallenges'] ?? [],
            $completedChallenges
        )));

        $mergedMissions = array_values(array_unique(array_merge(
            $sessionProgress['completedMissions'] ?? [],
            $completedMissions
        )));

        $result = [
            'activeChallenges'    => $mergedActive,
            'completedMissions'   => $mergedMissions,
            'points'              => $points,
            'completedChallenges' => $mergedCompleted,
        ];

        $_SESSION['swc_challenge_progress'] = $result;
        return $result;
    }

    /**
     * Daftarkan pengguna ke sebuah program tantangan air.
     */
    public function enrollChallenge(string $challengeCodeOrId, ?User $user): array
    {
        $challengeCode = trim($challengeCodeOrId);
        $challenge = Challenge::where('challenge_code', $challengeCode)
            ->orWhere('id', $challengeCode)
            ->first();

        if (!$challenge) {
            throw new \InvalidArgumentException('Tantangan tidak ditemukan di database.');
        }

        if ($user) {
            ChallengeParticipant::firstOrCreate(
                ['user_id' => $user->id, 'challenge_id' => $challenge->id],
                ['status' => 'ACTIVE', 'joined_at' => now()]
            );
        }

        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }
        if (!isset($_SESSION['swc_challenge_progress']['activeChallenges'])) {
            $_SESSION['swc_challenge_progress']['activeChallenges'] = [];
        }
        if (!in_array($challengeCode, $_SESSION['swc_challenge_progress']['activeChallenges'], true)) {
            $_SESSION['swc_challenge_progress']['activeChallenges'][] = $challengeCode;
        }

        $progress = $this->getUserProgress($user);

        return [
            'challenge' => $challenge,
            'progress'  => $progress,
        ];
    }

    /**
     * Selesaikan misi tantangan dengan validasi bukti dan pencegahan klaim poin ganda.
     */
    public function completeMission(
        string $challengeCodeOrId,
        string $missionCodeOrId,
        ?string $proofNotes = null,
        ?string $proofPhotoBase64 = null,
        ?User $user = null
    ): array {
        return DB::transaction(function () use ($challengeCodeOrId, $missionCodeOrId, $proofNotes, $proofPhotoBase64, $user) {
            $challengeCode = trim($challengeCodeOrId);
            $missionCode   = trim($missionCodeOrId);

            $mission   = ChallengeMission::where('mission_code', $missionCode)->orWhere('id', $missionCode)->first();
            $challenge = Challenge::where('challenge_code', $challengeCode)->orWhere('id', $challengeCode)->first() ?? $mission?->challenge;

            if (!$mission || !$challenge) {
                throw new \InvalidArgumentException('Misi atau tantangan tidak ditemukan.');
            }

            if ($user) {
                $participant = ChallengeParticipant::firstOrCreate(
                    ['user_id' => $user->id, 'challenge_id' => $challenge->id],
                    ['status' => 'ACTIVE', 'joined_at' => now()]
                );

                // Pencegahan klaim poin ganda jika misi sudah pernah diselesaikan
                $already = ChallengeSubmission::where('participant_id', $participant->id)
                    ->where('mission_id', $mission->id)
                    ->where('is_completed', true)
                    ->exists();

                if ($already) {
                    return [
                        'already'  => true,
                        'message'  => 'Misi ini telah diselesaikan sebelumnya!',
                        'progress' => $this->getUserProgress($user),
                    ];
                }

                $photoPath = null;
                if (!empty($proofPhotoBase64)) {
                    $photoPath = $this->saveProofPhoto($participant->id, $mission->id, $proofPhotoBase64);
                }

                ChallengeSubmission::create([
                    'participant_id'   => $participant->id,
                    'mission_id'       => $mission->id,
                    'is_completed'     => true,
                    'proof_notes'      => $proofNotes ?: 'Misi selesai diverifikasi',
                    'proof_photo_path' => $photoPath,
                    'completed_at'     => now(),
                ]);

                // Tambahkan poin misi ke profil pelanggan
                $profile = $user->customerProfile;
                if ($profile && $mission->xp > 0) {
                    $profile->increment('points', $mission->xp);
                }

                // Periksa apakah semua misi dalam program tantangan ini telah selesai
                $totalMissions = $challenge->missions()->count();
                $completedMissionsCount = ChallengeSubmission::where('participant_id', $participant->id)
                    ->where('is_completed', true)
                    ->count();

                if ($totalMissions > 0 && $completedMissionsCount >= $totalMissions) {
                    $participant->update([
                        'status'       => 'COMPLETED',
                        'completed_at' => now(),
                    ]);

                    $bonusPoints = $challenge->points_reward ?: 250;
                    if ($profile && $bonusPoints > 0) {
                        $profile->increment('points', $bonusPoints);
                    }

                    $this->notificationService->send(
                        $user->id,
                        'CHALLENGE',
                        "Selamat! Anda Menyelesaikan Tantangan \"{$challenge->title}\"",
                        "Seluruh misi konservasi air berhasil diselesaikan. Bonus total +{$bonusPoints} poin diserahkan kepada Anda.",
                        '/tantangan'
                    );
                }
            }

            if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
                @session_start();
            }
            if (!isset($_SESSION['swc_challenge_progress'])) {
                $_SESSION['swc_challenge_progress'] = [
                    'activeChallenges'    => [$challengeCode],
                    'completedMissions'   => [],
                    'points'              => 100,
                    'completedChallenges' => [],
                ];
            }
            if (!in_array($missionCode, $_SESSION['swc_challenge_progress']['completedMissions'], true)) {
                $_SESSION['swc_challenge_progress']['completedMissions'][] = $missionCode;
                $_SESSION['swc_challenge_progress']['points'] = (int)($_SESSION['swc_challenge_progress']['points'] ?? 100) + $mission->xp;
            }

            $progress = $this->getUserProgress($user);

            return [
                'already'  => false,
                'message'  => 'Selamat! Misi "' . $mission->title . '" berhasil diselesaikan (+' . $mission->xp . ' XP)!',
                'progress' => $progress,
            ];
        });
    }

    /**
     * Simpan foto bukti misi dari Base64 string ke storage publik.
     */
    protected function saveProofPhoto(int $participantId, int $missionId, string $base64Data): ?string
    {
        try {
            $ext = 'jpg';
            if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
                $base64Data = substr($base64Data, strpos($base64Data, ',') + 1);
                $ext = strtolower($type[1]);
                if ($ext === 'jpeg') $ext = 'jpg';
            }

            $decoded = base64_decode($base64Data, true);
            if ($decoded === false) {
                return null;
            }

            $filename = "proof_part_{$participantId}_m_{$missionId}_" . Str::random(8) . '.' . $ext;
            $path = 'challenges/' . $filename;

            Storage::disk('public')->put($path, $decoded);

            return 'storage/' . $path;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
