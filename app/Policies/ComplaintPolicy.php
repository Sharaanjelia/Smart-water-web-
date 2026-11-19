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
     * - Petugas Lapangan: hanya berhak jika tiket secara resmi ditugaskan kepada dirinya.
     */
    public function update(User $user, Complaint $complaint): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isFieldOfficer()) {
            return (int)$complaint->assigned_to === (int)$user->id;
        }

        return false;
    }

    /**
     * Menentukan apakah pengguna berhak menugaskan / mendisposisikan tiket pengaduan.
     * - Hanya Admin yang berwenang menugaskan / mendisposisikan tiket pengaduan.
     */
    public function assign(User $user, Complaint $complaint): bool
    {
        return $user->isAdmin();
    }

    /**
     * Menentukan apakah pengguna berhak memberikan tanggapan / catatan penanganan pada tiket.
     * - Admin: wewenang supervisi penuh.
     * - Petugas Lapangan: hanya petugas yang ditugaskan resmi pada tiket bersangkutan.
     */
    public function respond(User $user, Complaint $complaint): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isFieldOfficer()) {
            return (int)$complaint->assigned_to === (int)$user->id;
        }

        return false;
    }

    /**
     * Menentukan apakah pengguna berhak menyelesaikan atau menolak tiket pengaduan.
     * - Admin: wewenang supervisi penuh.
     * - Petugas Lapangan: hanya petugas yang ditugaskan resmi pada tiket bersangkutan.
     */
    public function resolve(User $user, Complaint $complaint): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isFieldOfficer()) {
            return (int)$complaint->assigned_to === (int)$user->id;
        }

        return false;
    }
}
