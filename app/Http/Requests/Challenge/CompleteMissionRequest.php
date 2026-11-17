<?php

namespace App\Http\Requests\Challenge;

use App\Http\Requests\BaseFormRequest;

class CompleteMissionRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $raw = $this->json()->all() ?: [];

        $challengeId = $raw['challengeId'] ?? $this->input('challengeId', $raw['challenge_id'] ?? $this->input('challenge_id'));
        $missionId   = $raw['missionId'] ?? $this->input('missionId', $raw['mission_id'] ?? $this->input('mission_id'));
        $proofNotes  = $raw['proofNotes'] ?? $this->input('proofNotes', $raw['notes'] ?? $this->input('notes'));
        $proofPhoto  = $raw['proofPhoto'] ?? $this->input('proofPhoto', $raw['photo'] ?? $this->input('photo', $raw['image'] ?? $this->input('image')));

        $this->merge([
            'challengeId' => is_string($challengeId) || is_numeric($challengeId) ? trim((string)$challengeId) : '',
            'missionId'   => is_string($missionId) || is_numeric($missionId) ? trim((string)$missionId) : '',
            'proofNotes'  => is_string($proofNotes) ? trim($proofNotes) : null,
            'proofPhoto'  => $proofPhoto,
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
            'challengeId' => 'required|string|max:50',
            'missionId'   => 'required|string|max:50',
            'proofNotes'  => 'nullable|string|max:1000',
            'proofPhoto'  => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if (!$this->isValidBase64Image($value)) {
                        $fail('Format foto bukti aksi tantangan tidak valid atau ukuran melebihi batas 10MB.');
                    }
                },
            ],
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
            'challengeId.required' => 'ID tantangan (challengeId) wajib diisi.',
            'missionId.required'   => 'ID misi tantangan (missionId) wajib diisi.',
        ];
    }
}
