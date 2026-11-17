<?php

namespace App\Policies;

use App\Models\User;
use App\Models\UserLessonProgress;
use App\Models\UserQuizResult;
use Illuminate\Auth\Access\HandlesAuthorization;

class AcademyProgressPolicy
{
    use HandlesAuthorization;

    /**
     * Menentukan apakah pengguna berhak melihat progres akademi (materi / kuis).
     */
    public function view(User $user, UserLessonProgress|UserQuizResult $progress): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return (int)$user->id === (int)$progress->user_id;
    }

    /**
     * Menentukan apakah pengguna berhak menyelesaikan materi pelajaran.
     */
    public function completeLesson(User $user): bool
    {
        return true;
    }

    /**
     * Menentukan apakah pengguna berhak menyelesaikan kuis materi.
     */
    public function completeQuiz(User $user): bool
    {
        return true;
    }
}
