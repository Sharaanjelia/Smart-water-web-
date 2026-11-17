<?php

namespace App\Http\Requests\Profile;

use App\Http\Requests\BaseFormRequest;

class UpdateProfileRequest extends BaseFormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->currentActor() !== null;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $raw = $this->json()->all() ?: [];

        $phone   = $raw['phone'] ?? $this->input('phone');
        $address = $raw['address'] ?? $this->input('address');
        $name    = $raw['name'] ?? $this->input('name');

        $this->merge([
            'phone'   => is_string($phone) ? trim($phone) : null,
            'address' => is_string($address) ? trim($address) : null,
            'name'    => is_string($name) ? trim($name) : null,
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
            'phone'   => 'nullable|string|max:30',
            'address' => 'nullable|string|max:255',
            'name'    => 'nullable|string|max:100',
        ];
    }
}
