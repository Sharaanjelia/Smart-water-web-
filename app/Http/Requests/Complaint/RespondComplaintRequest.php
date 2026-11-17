<?php

namespace App\Http\Requests\Complaint;

use App\Http\Requests\BaseFormRequest;

class RespondComplaintRequest extends BaseFormRequest
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

        $note  = $raw['note'] ?? $this->input('note', $raw['message'] ?? $this->input('message'));
        $actor = $raw['actor'] ?? $this->input('actor');

        $this->merge([
            'note'  => is_string($note) ? trim($note) : '',
            'actor' => is_string($actor) ? trim($actor) : null,
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
            'note'  => 'required|string|min:3|max:1000',
            'actor' => 'nullable|string|max:100',
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
            'note.required' => 'Tanggapan atau catatan penanganan tiket wajib diisi.',
            'note.min'      => 'Tanggapan minimal terdiri dari 3 karakter.',
        ];
    }
}
