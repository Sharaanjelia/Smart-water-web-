<?php

namespace App\Policies;

use App\Models\Complaint;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ComplaintPolicy
{
    use HandlesAuthorization;

    /**
     * Menentukan apakah pengguna berhak melihat detail tiket pengaduan tertentu.
     * - Tiket publik / anonim (user_id === null): dapat dilihat oleh siapa saja (termasuk tamu).
     * - Tiket privat (user_id !== null): hanya pelapor pemilik tiket atau staf/admin SCADA yang berhak.
     */
    public function view(?User $user, Complaint $complaint): bool
    {
        if ($complaint->user_id === null) {
            return true;
        }

        if (!$user) {
            return false;
        }

        if ($user->isStaff()) {
            return true;
        }

        return (int)$user->id === (int)$complaint->user_id;
    }

    /**
     * Menentukan apakah pengguna berhak membuat tiket pengaduan baru.
     * Terbuka untuk seluruh warga (tamu maupun terautentikasi).
     */
    public function create(?User $user): bool
    {
        return true;
    }

    /**
     * Menentukan apakah pengguna berhak memperbarui status tiket pengaduan.
     * - Admin: memiliki wewenang penuh atas seluruh tiket kota.
     * - Staf: hanya berhak jika tiket belum ditugaskan (klaim) atau ditugaskan kepada dirinya sendiri.
     * - Staf A dilarang memperbarui tiket yang ditugaskan kepada Staf B.
     */
    public function update(User $user, Complaint $complaint): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isStaff()) {
            if ($complaint->assigned_to === null) {
                return true;
            }
            return (int)$complaint->assigned_to === (int)$user->id;
        }

        return false;
    }

    /**
     * Menentukan apakah pengguna berhak menugaskan / mendisposisikan tiket pengaduan.
     * - Admin: berhak menugaskan/menugaskan ulang tiket apa pun.
     * - Staf: berhak jika tiket belum ditugaskan atau ditugaskan kepada dirinya sendiri.
     * - Staf lain tidak berhak merebut/menugaskan ulang tiket milik rekannya.
     */
    public function assign(User $user, Complaint $complaint): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isStaff()) {
            if ($complaint->assigned_to === null) {
                return true;
            }
            return (int)$complaint->assigned_to === (int)$user->id;
        }

        return false;
    }

    /**
     * Menentukan apakah pengguna berhak memberikan tanggapan / catatan penanganan pada tiket.
     * - Admin: wewenang supervisi penuh.
     * - Staf: hanya teknisi yang ditugaskan (atau belum ditugaskan).
     */
    public function respond(User $user, Complaint $complaint): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isStaff()) {
            if ($complaint->assigned_to === null) {
                return true;
            }
            return (int)$complaint->assigned_to === (int)$user->id;
        }

        return false;
    }

    /**
     * Menentukan apakah pengguna berhak menyelesaikan atau menolak tiket pengaduan.
     * - Admin: wewenang supervisi penuh.
     * - Staf: hanya teknisi yang ditugaskan resmi (atau belum ditugaskan).
     */
    public function resolve(User $user, Complaint $complaint): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isStaff()) {
            if ($complaint->assigned_to === null) {
                return true;
            }
            return (int)$complaint->assigned_to === (int)$user->id;
        }

        return false;
    }
}
