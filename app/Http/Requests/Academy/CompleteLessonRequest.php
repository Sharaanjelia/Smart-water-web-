<?php

namespace App\Http\Requests\Academy;

use App\Http\Requests\BaseFormRequest;

class CompleteLessonRequest extends BaseFormRequest
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

        $lessonId = $raw['lessonId'] ?? $this->input('lessonId', $raw['lesson_id'] ?? $this->input('lesson_id'));

        $this->merge([
            'lessonId' => is_string($lessonId) || is_numeric($lessonId) ? trim((string)$lessonId) : '',
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
            'lessonId' => 'required|string|max:50',
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
            'lessonId.required' => 'ID materi (lessonId) wajib diisi.',
        ];
    }
}
