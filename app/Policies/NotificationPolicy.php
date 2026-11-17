<?php

namespace App\Policies;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class NotificationPolicy
{
    use HandlesAuthorization;

    /**
     * Menentukan apakah pengguna berhak melihat pesan notifikasi tertentu.
     */
    public function view(User $user, Notification $notification): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return (int)$user->id === (int)$notification->user_id;
    }

    /**
     * Menentukan apakah pengguna berhak menandai atau mengubah notifikasi.
     * Hanya pemilik notifikasi yang berhak.
     */
    public function update(User $user, Notification $notification): bool
    {
        return (int)$user->id === (int)$notification->user_id;
    }
}
