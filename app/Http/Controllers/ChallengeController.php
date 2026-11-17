<?php

namespace App\Http\Controllers;

use App\Http\Requests\Challenge\CompleteMissionRequest;
use App\Http\Requests\Challenge\EnrollChallengeRequest;
use App\Http\Resources\ChallengeResource;
use App\Models\Challenge;
use App\Models\User;
use App\Services\ChallengeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ChallengeController extends Controller
{
    public function __construct(
        protected ChallengeService $challengeService
    ) {}

    /**
     * Proxy static method untuk backward compatibility.
     */
    public static function getUserProgress(?User $user): array
    {
        return app(ChallengeService::class)->getUserProgress($user);
    }

    /**
     * Daftar seluruh tantangan hemat air via ChallengeResource dan ChallengeService.
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $data = $this->challengeService->getActiveChallenges($user);

        return response()->json([
            'ok'         => true,
            'challenges' => ChallengeResource::collection($data['challenges']),
            'progress'   => $data['progress'],
        ]);
    }

    /**
     * Bergabung ke salah satu tantangan via ChallengeService.
     */
    public function enroll(EnrollChallengeRequest $request): JsonResponse
    {
        $challengeCode = trim((string)$request->input('challengeId', ''));
        $user = Auth::user();

        try {
            $data = $this->challengeService->enrollChallenge($challengeCode, $user);

            return response()->json([
                'ok'        => true,
                'message'   => 'Berhasil bergabung ke: ' . $data['challenge']->title . '!',
                'challenge' => new ChallengeResource($data['challenge']),
                'progress'  => $data['progress'],
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }

    /**
     * Tandai sebuah misi tantangan selesai via ChallengeService.
     */
    public function completeMission(CompleteMissionRequest $request): JsonResponse
    {
        $challengeCode = trim((string)$request->input('challengeId', ''));
        $missionCode   = trim((string)$request->input('missionId', ''));
        $proofNotes    = $request->input('proofNotes', $request->input('proof_notes'));
        $proofPhoto    = $request->input('proofPhoto', $request->input('photo'));
        $user          = Auth::user();

        try {
            $result = $this->challengeService->completeMission(
                $challengeCode,
                $missionCode,
                $proofNotes,
                $proofPhoto,
                $user
            );

            if (!empty($result['already'])) {
                return response()->json([
                    'ok'       => true,
                    'already'  => true,
                    'message'  => $result['message'],
                    'progress' => $result['progress'],
                ]);
            }

            return response()->json([
                'ok'       => true,
                'message'  => $result['message'],
                'progress' => $result['progress'],
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['error' => $e->getMessage()], 404);
        }
    }
}
