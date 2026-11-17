<?php

namespace App\Http\Requests\Auth;

use App\Http\Requests\BaseFormRequest;

class RegisterCustomerRequest extends BaseFormRequest
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
        $this->merge([
            'name'     => is_string($this->input('name')) ? trim($this->input('name')) : '',
            'email'    => is_string($this->input('email')) ? strtolower(trim($this->input('email'))) : '',
            'phone'    => is_string($this->input('phone')) ? trim($this->input('phone')) : null,
            'password' => (string)$this->input('password'),
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
            'name'            => 'required|string|min:2|max:100',
            'email'           => 'required|email|max:150|unique:users,email',
            'phone'           => 'nullable|string|max:30',
            'password'        => 'required|string|min:6|max:100',
            'confirmPassword' => 'nullable|string|same:password',
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
            'name.required'        => 'Nama lengkap wajib diisi.',
            'name.min'             => 'Nama lengkap minimal 2 karakter.',
            'email.required'       => 'Alamat email wajib diisi.',
            'email.email'          => 'Format alamat email tidak valid.',
            'email.unique'         => 'Alamat email sudah terdaftar di sistem Smart Water.',
            'password.required'    => 'Kata sandi akun wajib diisi.',
            'password.min'         => 'Kata sandi minimal terdiri dari 6 karakter.',
            'confirmPassword.same' => 'Konfirmasi kata sandi tidak cocok.',
        ];
    }
}
