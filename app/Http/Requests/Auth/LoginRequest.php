<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseFormRequest;

class LoginRequest extends BaseFormRequest
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
        $identifier = $this->input('identifier');
        if (empty($identifier) && $this->filled('email')) {
            $identifier = $this->input('email');
        } elseif (empty($identifier) && $this->filled('phone')) {
            $identifier = $this->input('phone');
        } elseif (empty($identifier) && $this->filled('meter')) {
            $identifier = $this->input('meter');
        }

        $this->merge([
            'identifier' => is_string($identifier) ? trim($identifier) : '',
            'password'   => is_string($this->input('password')) ? $this->input('password') : '',
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
            'identifier' => 'required|string|min:2|max:100',
            'password'   => 'required|string|min:1',
            'is_demo'    => 'nullable|boolean',
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
            'identifier.required' => 'Identitas (email, telepon, nomor pelanggan, atau nomor meter) wajib diisi.',
            'identifier.min'      => 'Identitas minimal terdiri dari 2 karakter.',
            'password.required'   => 'Kata sandi wajib diisi.',
        ];
    }
}
