<?php

namespace App\Http\Controllers;

use App\Http\Requests\Academy\CompleteLessonRequest;
use App\Http\Requests\Academy\CompleteQuizRequest;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Models\User;
use App\Services\AcademyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AcademyController extends Controller
{
    public function __construct(
        protected AcademyService $academyService
    ) {}

    /**
     * Proxy static method untuk backward compatibility.
     */
    public static function getUserProgress(?User $user): array
    {
        return app(AcademyService::class)->getUserProgress($user);
    }

    /**
     * Tampilkan daftar kursus via CourseResource dan progres pengguna via AcademyService.
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $curriculum = $this->academyService->getCurriculum($user);

        return response()->json([
            'ok'       => true,
            'courses'  => CourseResource::collection($curriculum['courses']),
            'progress' => $curriculum['progress'],
        ]);
    }

    /**
     * Tandai sebuah bab materi selesai dibaca via AcademyService.
     */
    public function completeLesson(CompleteLessonRequest $request): JsonResponse
    {
        $lessonCode = trim((string)$request->input('lessonId', ''));
        $user = Auth::user();

        $progress = $this->academyService->completeLesson($lessonCode, $user);

        return response()->json([
            'ok'       => true,
            'progress' => $progress,
        ]);
    }

    /**
     * Simpan hasil skor kuis materi akademi via AcademyService.
     */
    public function completeQuiz(CompleteQuizRequest $request): JsonResponse
    {
        $courseCode = trim((string)$request->input('courseId', ''));
        $score      = max(0, min(100, (int)$request->input('score', 0)));
        $xpReward   = max(0, (int)$request->input('xpReward', 0));
        $user       = Auth::user();

        $result = $this->academyService->completeQuiz($courseCode, $score, $xpReward, $user);

        return response()->json([
            'ok'       => true,
            'progress' => $result['progress'],
        ]);
    }
}
