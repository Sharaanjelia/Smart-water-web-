<?php

namespace App\Policies;

use App\Models\MeterReading;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MeterReadingPolicy
{
    use HandlesAuthorization;

    /**
     * Menentukan apakah pengguna berhak melihat daftar laporan pembacaan meter.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Menentukan apakah pengguna berhak melihat detail laporan meter tertentu.
     * Hanya pemilik laporan atau petugas/admin yang berhak.
     */
    public function view(?User $user, MeterReading $meterReading): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->isAdmin() || $user->isScadaOperator()) {
            return true;
        }

        return (int)$user->id === (int)$meterReading->user_id;
    }

    /**
     * Menentukan apakah pengguna berhak membuat laporan stand meter baru.
     */
    public function create(?User $user): bool
    {
        return true;
    }

    /**
     * Menentukan apakah pengguna berhak memvalidasi atau menolak laporan meter.
     * Hanya Administrator dan Operator SCADA yang memiliki wewenang ini.
     * Role legacy staff dan petugas lapangan dilarang keras.
     */
    public function validate(User $user, MeterReading $meterReading): bool
    {
        return $user->isAdmin() || $user->isScadaOperator();
    }
}
