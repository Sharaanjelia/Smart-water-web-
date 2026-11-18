<?php

namespace App\Http\Requests;

use App\Models\Bill;
use App\Models\Payment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreManualPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $billId = $this->input('bill_id') ?? $this->route('bill');
        if (is_object($billId)) {
            $billId = $billId->id;
        }

        if (!$billId) {
            return false;
        }

        $bill = Bill::find($billId);
        if (!$bill) {
            return false;
        }

        // Only the owner of the bill or admin/staff can submit payment
        return $this->user()->id === $bill->user_id || $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'bill_id' => ['required', 'exists:bills,id'],
            'amount' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'string', 'max:50'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'sender_name' => ['required', 'string', 'max:150'],
            'sender_account_number' => ['nullable', 'string', 'max:60'],
            'proof_photo' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'reference_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Configure additional validation callbacks.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $billId = $this->input('bill_id');
            if (!$billId) {
                return;
            }

            $bill = Bill::find($billId);
            if (!$bill) {
                return;
            }

            // SEC-BILL-05: Cek apakah tagihan sudah lunas
            if ($bill->status === 'PAID') {
                $validator->errors()->add('bill_id', 'Tagihan ini telah lunas dan tidak dapat dibayar kembali.');
                return;
            }

            // SEC-BILL-05: Cegah pengajuan ganda jika masih ada pembayaran berstatus PENDING
            $hasPendingPayment = Payment::where('bill_id', $bill->id)
                ->where('status', 'PENDING')
                ->exists();

            if ($hasPendingPayment) {
                $validator->errors()->add('bill_id', 'Masih terdapat pembayaran pending yang menunggu verifikasi admin untuk tagihan ini.');
            }

            // SEC-BILL-04: Validasi kecocokan nominal pembayaran dengan total tagihan
            $inputAmount = (float) $this->input('amount');
            $billTotal = (float) $bill->total_amount;

            if (round($inputAmount, 2) !== round($billTotal, 2)) {
                $validator->errors()->add('amount', 'Nominal transfer (Rp ' . number_format($inputAmount, 0, ',', '.') . ') tidak sesuai dengan total tagihan resmi (Rp ' . number_format($billTotal, 0, ',', '.') . ').');
            }
        });
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'bill_id.required' => 'ID tagihan wajib dicantumkan.',
            'bill_id.exists' => 'Tagihan yang dimaksud tidak ditemukan dalam sistem.',
            'amount.required' => 'Nominal pembayaran wajib diisi.',
            'amount.numeric' => 'Nominal pembayaran harus berupa angka.',
            'sender_name.required' => 'Nama pemilik rekening / pengirim wajib diisi.',
            'proof_photo.required' => 'Bukti struk transfer atau pembayaran wajib diunggah.',
            'proof_photo.file' => 'Bukti pembayaran harus berupa berkas yang valid.',
            'proof_photo.mimes' => 'Format berkas bukti pembayaran harus JPG, JPEG, PNG, atau PDF.',
            'proof_photo.max' => 'Ukuran berkas bukti pembayaran maksimal 5 MB.',
        ];
    }
}
