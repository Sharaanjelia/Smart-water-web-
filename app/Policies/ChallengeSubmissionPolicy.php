<?php

namespace App\Policies;

use App\Models\ChallengeSubmission;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChallengeSubmissionPolicy
{
    use HandlesAuthorization;

    /**
     * Menentukan apakah pengguna berhak melihat bukti pengumpulan misi tantangan.
     */
    public function view(?User $user, ChallengeSubmission $submission): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        $participant = $submission->participant;
        return $participant && (int)$user->id === (int)$participant->user_id;
    }

    /**
     * Menentukan apakah pengguna berhak memperbarui atau mengumpulkan misi.
     */
    public function update(User $user, ChallengeSubmission $submission): bool
    {
        $participant = $submission->participant;
        return $participant && (int)$user->id === (int)$participant->user_id;
    }
}
