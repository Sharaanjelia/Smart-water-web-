<?php

namespace App\Http\Requests\Academy;

use App\Http\Requests\BaseFormRequest;

class CompleteQuizRequest extends BaseFormRequest
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

        $courseId = $raw['courseId'] ?? $this->input('courseId', $raw['course_id'] ?? $this->input('course_id'));
        $score    = $raw['score'] ?? $this->input('score');
        $passed   = $raw['passed'] ?? $this->input('passed');

        $this->merge([
            'courseId' => is_string($courseId) || is_numeric($courseId) ? trim((string)$courseId) : '',
            'score'    => is_numeric($score) ? (float)$score : $score,
            'passed'   => $passed !== null ? filter_var($passed, FILTER_VALIDATE_BOOLEAN) : null,
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
            'courseId' => 'required|string|max:50',
            'score'    => 'required|numeric|min:0|max:100',
            'passed'   => 'nullable|boolean',
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
            'courseId.required' => 'ID kursus (courseId) wajib diisi.',
            'score.required'    => 'Nilai skor kuis wajib diisi.',
            'score.numeric'     => 'Nilai skor kuis harus berupa angka.',
            'score.min'         => 'Nilai skor minimal 0.',
            'score.max'         => 'Nilai skor maksimal 100.',
        ];
    }
}
