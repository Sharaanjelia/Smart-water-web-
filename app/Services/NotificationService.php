<?php

namespace App\Services;

use App\Models\Notification;
use Illuminate\Database\Eloquent\Collection;

class NotificationService
{
    /**
     * Kirim dan simpan notifikasi baru untuk pengguna tertentu.
     */
    public function send(
        int $userId,
        string $type,
        string $title,
        string $desc,
        ?string $link = null
    ): Notification {
        return Notification::create([
            'user_id' => $userId,
            'type'    => strtoupper($type),
            'title'   => $title,
            'desc'    => $desc,
            'link'    => $link,
            'is_read' => false,
        ]);
    }

    /**
     * Dapatkan daftar notifikasi pengguna dengan batas jumlah tertentu.
     *
     * @return Collection<int, Notification>
     */
    public function getForUser(int $userId, int $limit = 20): Collection
    {
        return Notification::where('user_id', $userId)
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * Hitung jumlah notifikasi yang belum dibaca.
     */
    public function getUnreadCount(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Tandai seluruh notifikasi milik pengguna sebagai sudah dibaca.
     */
    public function markAllAsRead(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Tandai satu notifikasi tertentu sebagai sudah dibaca.
     */
    public function markAsRead(int $notificationId, int $userId): bool
    {
        $notification = Notification::where('id', $notificationId)
            ->where('user_id', $userId)
            ->first();

        if (!$notification) {
            return false;
        }

        return $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }
}
