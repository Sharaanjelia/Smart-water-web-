<?php

namespace App\Http\Requests\Complaint;

use App\Http\Requests\BaseFormRequest;

class AssignComplaintRequest extends BaseFormRequest
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

        $staffId = $raw['staff_id'] ?? $this->input('staff_id', $raw['staffId'] ?? $this->input('staffId'));
        $note    = $raw['note'] ?? $this->input('note');

        $this->merge([
            'staff_id' => $staffId,
            'note'     => is_string($note) ? trim($note) : null,
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
            'staff_id' => 'required|exists:users,id',
            'note'     => 'nullable|string|max:500',
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
            'staff_id.required' => 'Petugas atau teknisi yang ditugaskan wajib dipilih.',
            'staff_id.exists'   => 'Petugas yang dipilih tidak ditemukan dalam sistem.',
        ];
    }
}
