<?php

namespace App\Http\Requests\Complaint;

use App\Http\Requests\BaseFormRequest;

class ResolveComplaintRequest extends BaseFormRequest
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

        $status           = $raw['status'] ?? $this->input('status', 'SELESAI');
        $resolutionNotes  = $raw['resolution_notes'] ?? $this->input('resolution_notes', $raw['notes'] ?? $this->input('notes'));
        $rejectionReason  = $raw['rejection_reason'] ?? $this->input('rejection_reason', $raw['reason'] ?? $this->input('reason'));
        $actor            = $raw['actor'] ?? $this->input('actor');

        $this->merge([
            'status'           => is_string($status) ? strtoupper(trim($status)) : 'SELESAI',
            'resolution_notes' => is_string($resolutionNotes) ? trim($resolutionNotes) : null,
            'rejection_reason' => is_string($rejectionReason) ? trim($rejectionReason) : null,
            'actor'            => is_string($actor) ? trim($actor) : null,
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
            'status'           => 'required|string|in:SELESAI,DITOLAK',
            'resolution_notes' => 'nullable|string|max:1000',
            'rejection_reason' => 'nullable|string|max:1000',
            'actor'            => 'nullable|string|max:100',
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
            'status.required' => 'Status penyelesaian (SELESAI / DITOLAK) wajib ditentukan.',
            'status.in'       => 'Status harus bernilai SELESAI atau DITOLAK.',
        ];
    }
}
