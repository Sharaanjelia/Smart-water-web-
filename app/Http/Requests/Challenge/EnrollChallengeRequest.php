<?php

namespace App\Http\Requests\Challenge;

use App\Http\Requests\BaseFormRequest;

class EnrollChallengeRequest extends BaseFormRequest
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

        $this->merge([
            'challengeId' => is_string($challengeId) || is_numeric($challengeId) ? trim((string)$challengeId) : '',
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
        ];
    }
}
