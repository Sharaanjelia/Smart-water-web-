<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VerifyPaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only Admin can verify/reject payments
        return $this->user() && $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:VERIFIED,REJECTED'],
            'rejection_reason' => ['required_if:status,REJECTED', 'nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Status keputusan verifikasi wajib dipilih.',
            'status.in' => 'Status keputusan harus VERIFIED atau REJECTED.',
            'rejection_reason.required_if' => 'Alasan penolakan wajib dicantumkan jika pembayaran ditolak.',
        ];
    }
}
