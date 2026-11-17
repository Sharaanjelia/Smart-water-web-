<?php

namespace App\Http\Requests\Meter;

use App\Http\Requests\BaseFormRequest;

class ValidateMeterReadingRequest extends BaseFormRequest
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

        $id     = $raw['id'] ?? $this->input('id', $raw['report_id'] ?? $this->input('report_id', $raw['report_number'] ?? $this->input('report_number')));
        $action = $raw['action'] ?? $this->input('action', $raw['status'] ?? $this->input('status', 'APPROVE'));
        $reason = $raw['reason'] ?? $this->input('reason', $raw['rejection_reason'] ?? $this->input('rejection_reason'));

        $this->merge([
            'id'     => is_string($id) || is_numeric($id) ? trim((string)$id) : '',
            'action' => is_string($action) ? trim($action) : 'APPROVE',
            'reason' => is_string($reason) ? trim($reason) : null,
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
            'id'     => 'required|string|max:100',
            'action' => 'nullable|string|max:50',
            'reason' => 'nullable|string|max:500',
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
            'id.required' => 'ID laporan pembacaan meter wajib diisi.',
        ];
    }
}
