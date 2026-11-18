<?php

namespace App\Policies;

use App\Models\Bill;
use App\Models\User;

class BillPolicy
{
    /**
     * Determine whether the user can view any bills.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the bill.
     */
    public function view(User $user, Bill $bill): bool
    {
        if ($user->isAdmin() || $user->isStaff()) {
            return true;
        }

        return $user->id === $bill->user_id;
    }

    /**
     * Determine whether the user can make payment for the bill.
     */
    public function pay(User $user, Bill $bill): bool
    {
        // Tagihan yang sudah lunas atau simulasi tidak dapat dibayar
        if ($bill->status === 'PAID' || $bill->isSimulation() || $bill->bill_type === 'SIMULATION') {
            return false;
        }

        // Tagihan yang sedang menunggu verifikasi tidak dapat dibayar ulang
        if ($bill->status === 'PENDING_VERIFICATION') {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        return $user->id === $bill->user_id;
    }
}
