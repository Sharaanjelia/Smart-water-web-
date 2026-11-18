<?php

namespace App\Services;

use App\Models\Bill;
use App\Models\Notification;
use App\Models\Payment;
use App\Models\PaymentHistory;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PaymentService
{
    /**
     * Submit a manual payment with proof of payment.
     * Status becomes PENDING, and bill status becomes PENDING_VERIFICATION (NOT PAID).
     */
    public function submitManualPayment(Bill $bill, int $userId, array $data, UploadedFile $proofFile): Payment
    {
        if ($bill->status === 'PAID') {
            throw ValidationException::withMessages([
                'bill' => 'Tagihan ini sudah berstatus lunas (PAID).',
            ]);
        }

        return DB::transaction(function () use ($bill, $userId, $data, $proofFile) {
            // Store uploaded proof photo
            $path = $proofFile->store('payment_proofs', 'public');

            $paymentNumber = 'PAY-PYK-' . now()->format('Ym') . '-' . strtoupper(substr(uniqid(), -6));

            $payment = Payment::create([
                'payment_number' => $paymentNumber,
                'bill_id' => $bill->id,
                'user_id' => $userId,
                'amount' => $data['amount'] ?? $bill->total_amount,
                'payment_method' => $data['payment_method'] ?? 'manual_transfer',
                'bank_name' => $data['bank_name'] ?? null,
                'sender_name' => $data['sender_name'] ?? null,
                'sender_account_number' => $data['sender_account_number'] ?? null,
                'proof_photo_path' => $path,
                'reference_number' => $data['reference_number'] ?? null,
                'status' => 'PENDING',
                'notes' => $data['notes'] ?? null,
            ]);

            // Bill status changes to PENDING_VERIFICATION (strictly NOT PAID)
            $bill->update([
                'status' => 'PENDING_VERIFICATION',
            ]);

            // Record history audit log
            PaymentHistory::create([
                'payment_id' => $payment->id,
                'previous_status' => null,
                'new_status' => 'PENDING',
                'actor_id' => $userId,
                'actor_role' => 'customer',
                'action_note' => 'Pengajuan bukti transfer pembayaran manual oleh pelanggan.',
            ]);

            // Notification for customer
            Notification::create([
                'user_id' => $userId,
                'type' => 'billing',
                'title' => 'Bukti Pembayaran Diterima',
                'desc' => "Bukti pembayaran untuk tagihan {$bill->bill_number} telah diterima dan sedang menunggu verifikasi Admin PDAM.",
                'link' => '/bill',
                'is_read' => false,
            ]);

            return $payment->load('histories');
        });
    }

    /**
     * Verify payment by Admin.
     * ONLY HERE does the bill status change to PAID.
     */
    public function verifyPayment(Payment $payment, int $verifierId, ?string $notes = null): bool
    {
        if ($payment->status === 'VERIFIED') {
            return true;
        }

        return DB::transaction(function () use ($payment, $verifierId, $notes) {
            $prevStatus = $payment->status;

            // 1. Update Payment status to VERIFIED
            $payment->update([
                'status' => 'VERIFIED',
                'verified_by' => $verifierId,
                'verified_at' => now(),
                'notes' => $notes ?: $payment->notes,
            ]);

            // 2. ONLY HERE: Change Bill status to PAID
            $bill = $payment->bill;
            $bill->update([
                'status' => 'PAID',
                'paid_at' => now(),
            ]);

            // 3. Record history
            PaymentHistory::create([
                'payment_id' => $payment->id,
                'previous_status' => $prevStatus,
                'new_status' => 'VERIFIED',
                'actor_id' => $verifierId,
                'actor_role' => 'admin',
                'action_note' => 'Pembayaran diverifikasi sah oleh Admin PDAM. Status tagihan berubah menjadi LUNAS (PAID).' . ($notes ? " Catatan: {$notes}" : ''),
            ]);

            // 4. Send notification to customer
            Notification::create([
                'user_id' => $payment->user_id,
                'type' => 'billing',
                'title' => 'Pembayaran Terverifikasi (Lunas)',
                'desc' => "Pembayaran tagihan air {$bill->bill_number} sebesar Rp " . number_format($payment->amount, 0, ',', '.') . " telah diverifikasi sah oleh Admin PDAM. Terima kasih.",
                'link' => '/bill',
                'is_read' => false,
            ]);

            return true;
        });
    }

    /**
     * Reject payment by Admin.
     * Bill status reverts to UNPAID so the customer can re-submit a valid receipt.
     */
    public function rejectPayment(Payment $payment, int $verifierId, string $reason): bool
    {
        return DB::transaction(function () use ($payment, $verifierId, $reason) {
            $prevStatus = $payment->status;

            $payment->update([
                'status' => 'REJECTED',
                'verified_by' => $verifierId,
                'verified_at' => now(),
                'rejection_reason' => $reason,
            ]);

            // Revert bill status to UNPAID
            $bill = $payment->bill;
            $bill->update([
                'status' => 'UNPAID',
            ]);

            // Record history
            PaymentHistory::create([
                'payment_id' => $payment->id,
                'previous_status' => $prevStatus,
                'new_status' => 'REJECTED',
                'actor_id' => $verifierId,
                'actor_role' => 'admin',
                'action_note' => "Pembayaran ditolak oleh Admin PDAM. Alasan: {$reason}",
            ]);

            // Send notification to customer
            Notification::create([
                'user_id' => $payment->user_id,
                'type' => 'billing',
                'title' => 'Pembayaran Ditolak',
                'desc' => "Bukti pembayaran tagihan {$bill->bill_number} belum dapat diverifikasi. Alasan: {$reason}. Silakan periksa kembali dan unggah ulang bukti yang sah.",
                'link' => '/bill',
                'is_read' => false,
            ]);

            return true;
        });
    }

    /**
     * List payments for admin/staff review.
     */
    public function listPayments(?string $status = null, int $perPage = 15)
    {
        $query = Payment::with(['bill', 'user', 'verifier'])
            ->orderBy('id', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        return $query->paginate($perPage);
    }
}
