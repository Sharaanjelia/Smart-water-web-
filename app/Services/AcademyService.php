<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Lesson;
use App\Models\User;
use App\Models\UserLessonProgress;
use App\Models\UserQuizResult;
use Illuminate\Support\Facades\DB;

class AcademyService
{
    public function __construct(
        protected NotificationService $notificationService
    ) {}

    /**
     * Dapatkan kurikulum akademi air beserta progres peserta.
     */
    public function getCurriculum(?User $user): array
    {
        $courses = Course::with('lessons')->where('is_published', true)->get();
        $progress = $this->getUserProgress($user);

        return [
            'courses'  => $courses,
            'progress' => $progress,
        ];
    }

    /**
     * Ambil data progres akademi pengguna saat ini dari database dan fallback session.
     */
    public function getUserProgress(?User $user): array
    {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }

        $sessionProgress = $_SESSION['swc_academy_progress'] ?? [
            'completedLessons' => ['lesson-1-1'],
            'completedQuizzes' => [],
            'points'           => 100,
            'badges'           => [],
        ];

        if (!$user) {
            return $sessionProgress;
        }

        // Ambil data pelajaran yang selesai dari database
        $completedLessons = UserLessonProgress::where('user_id', $user->id)
            ->where('is_completed', true)
            ->with('lesson')
            ->get()
            ->map(fn($p) => $p->lesson?->lesson_code ?? (string)$p->lesson_id)
            ->filter()
            ->values()
            ->toArray();

        // Ambil kuis yang sudah diselesaikan
        $quizResults = UserQuizResult::where('user_id', $user->id)
            ->with('course')
            ->get();

        $completedQuizzes = [];
        $badges = [];
        foreach ($quizResults as $qr) {
            $code = $qr->course?->course_code ?? (string)$qr->course_id;
            $completedQuizzes[$code] = $qr->score;

            if ($code === 'course-1' && $qr->score === 100) {
                $badges[] = 'badge-sago-guardian';
            }
        }

        if (count($completedQuizzes) >= 3) {
            $badges[] = 'badge-water-scholar';
        }

        $profile = $user->customerProfile;
        $points = $profile?->points ?? (int)($sessionProgress['points'] ?? 100);

        $mergedCompletedLessons = array_values(array_unique(array_merge(
            $sessionProgress['completedLessons'] ?? [],
            $completedLessons
        )));

        $mergedBadges = array_values(array_unique(array_merge(
            $sessionProgress['badges'] ?? [],
            $badges
        )));

        $mergedQuizzes = array_merge(
            $sessionProgress['completedQuizzes'] ?? [],
            $completedQuizzes
        );

        $result = [
            'completedLessons' => $mergedCompletedLessons,
            'completedQuizzes' => $mergedQuizzes,
            'points'           => $points,
            'badges'           => $mergedBadges,
        ];

        $_SESSION['swc_academy_progress'] = $result;
        return $result;
    }

    /**
     * Tandai sebuah bab materi selesai dibaca oleh pengguna.
     */
    public function completeLesson(string $lessonCodeOrId, ?User $user): array
    {
        $lessonCode = trim($lessonCodeOrId);

        if ($user) {
            $lesson = Lesson::where('lesson_code', $lessonCode)->orWhere('id', $lessonCode)->first();
            if ($lesson) {
                UserLessonProgress::updateOrCreate(
                    ['user_id' => $user->id, 'lesson_id' => $lesson->id],
                    ['is_completed' => true, 'completed_at' => now()]
                );
            }
        }

        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            @session_start();
        }
        if (!isset($_SESSION['swc_academy_progress']['completedLessons'])) {
            $_SESSION['swc_academy_progress']['completedLessons'] = [];
        }
        if (!in_array($lessonCode, $_SESSION['swc_academy_progress']['completedLessons'], true)) {
            $_SESSION['swc_academy_progress']['completedLessons'][] = $lessonCode;
        }

        return $this->getUserProgress($user);
    }

    /**
     * Simpan hasil skor kuis materi akademi dan kelola perolehan XP tanpa duplikasi poin.
     */
    public function completeQuiz(string $courseCodeOrId, int $score, ?int $xpReward = 0, ?User $user = null): array
    {
        return DB::transaction(function () use ($courseCodeOrId, $score, $xpReward, $user) {
            $courseCode = trim($courseCodeOrId);
            $score = max(0, min(100, $score));
            $isPassed = $score >= 70;
            $xpAwarded = max(0, (int)($xpReward ?: ($isPassed ? 100 : 25)));

            $course = Course::where('course_code', $courseCode)->orWhere('id', $courseCode)->first();

            if ($user && $course) {
                // Periksa apakah kuis kursus ini sudah pernah lulus sebelumnya (pencegahan duplikasi poin ganda)
                $previousPass = UserQuizResult::where('user_id', $user->id)
                    ->where('course_id', $course->id)
                    ->where('is_passed', true)
                    ->exists();

                // Simpan atau perbarui riwayat nilai kuis
                UserQuizResult::updateOrCreate(
                    ['user_id' => $user->id, 'course_id' => $course->id],
                    [
                        'score'        => $score,
                        'xp_earned'    => $xpAwarded,
                        'is_passed'    => $isPassed,
                        'completed_at' => now(),
                    ]
                );

                // Tambahkan poin ke profil hanya jika lulus untuk pertama kalinya
                if ($isPassed && !$previousPass && $user->customerProfile) {
                    $user->customerProfile->increment('points', $xpAwarded);

                    $this->notificationService->send(
                        $user->id,
                        'ACADEMY',
                        "Selamat! Anda Lulus Kuis \"{$course->title}\"",
                        "Anda memperoleh tambahan +{$xpAwarded} XP poin reward konservasi air.",
                        '/akademi-air'
                    );
                }
            }

            if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
                @session_start();
            }
            if (!isset($_SESSION['swc_academy_progress']['completedQuizzes'])) {
                $_SESSION['swc_academy_progress']['completedQuizzes'] = [];
            }
            $_SESSION['swc_academy_progress']['completedQuizzes'][$courseCode] = $score;

            $progress = $this->getUserProgress($user);

            return [
                'score'    => $score,
                'isPassed' => $isPassed,
                'progress' => $progress,
            ];
        });
    }
}
