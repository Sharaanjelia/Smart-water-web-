<?php

namespace App\Http\Requests\Complaint;

use App\Http\Requests\BaseFormRequest;

class UpdateComplaintStatusRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->isStaffOrAdmin();
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $raw = $this->json()->all() ?: [];

        $status = $raw['status'] ?? $this->input('status');
        $note   = $raw['note'] ?? $this->input('note');
        $actor  = $raw['actor'] ?? $this->input('actor');

        $this->merge([
            'status' => is_string($status) ? strtoupper(trim($status)) : '',
            'note'   => is_string($note) ? trim($note) : null,
            'actor'  => is_string($actor) ? trim($actor) : null,
        ]);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'status' => 'required|string|in:BARU,DIPROSES,MENUNGGU TINDAK LANJUT,SELESAI,DITOLAK',
            'note'   => 'nullable|string|max:1000',
            'actor'  => 'nullable|string|max:100',
        ];
    }

    /**
     * Custom validation error messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'status.required' => 'Status pengaduan wajib diisi.',
            'status.in'       => 'Pilihan status pengaduan tidak valid.',
        ];
    }
}
