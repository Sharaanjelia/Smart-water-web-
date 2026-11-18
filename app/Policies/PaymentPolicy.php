<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    /**
     * Determine whether the user can view any payments.
     */
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $user->isStaff();
    }

    /**
     * Determine whether the user can view the payment.
     */
    public function view(User $user, Payment $payment): bool
    {
        if ($user->isAdmin() || $user->isStaff()) {
            return true;
        }

        return $user->id === $payment->user_id;
    }

    /**
     * Determine whether the user can create a payment.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can verify or reject the payment.
     */
    public function verify(User $user, Payment $payment): bool
    {
        return $user->isAdmin() || $user->isStaff();
    }
}
