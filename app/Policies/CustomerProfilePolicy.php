<?php

namespace App\Policies;

use App\Models\CustomerProfile;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomerProfilePolicy
{
    use HandlesAuthorization;

    /**
     * Menentukan apakah pengguna berhak melihat profil pelanggan tertentu.
     * Hanya pemilik akun atau administrator yang berhak.
     */
    public function view(User $user, CustomerProfile $profile): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return (int)$user->id === (int)$profile->user_id;
    }

    /**
     * Menentukan apakah pengguna berhak memperbarui profil pelanggan.
     * Hanya pemilik akun atau administrator yang berhak.
     */
    public function update(User $user, CustomerProfile $profile): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return (int)$user->id === (int)$profile->user_id;
    }
}
