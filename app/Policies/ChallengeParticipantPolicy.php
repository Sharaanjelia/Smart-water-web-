<?php

namespace App\Policies;

use App\Models\ChallengeParticipant;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ChallengeParticipantPolicy
{
    use HandlesAuthorization;

    /**
     * Menentukan apakah pengguna berhak melihat status partisipasi tantangan.
     */
    public function view(?User $user, ChallengeParticipant $participant): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return (int)$user->id === (int)$participant->user_id;
    }

    /**
     * Menentukan apakah pengguna berhak mendaftar ke tantangan.
     */
    public function create(User $user): bool
    {
        return true;
    }
}
