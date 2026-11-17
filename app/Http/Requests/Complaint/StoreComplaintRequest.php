<?php

namespace App\Http\Requests\Complaint;

use App\Http\Requests\BaseFormRequest;

class StoreComplaintRequest extends BaseFormRequest
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
        $merges = [];

        $category = $raw['category'] ?? $this->input('category', $raw['category_id'] ?? $this->input('category_id'));
        if ($category !== null) {
            $merges['category'] = is_string($category) ? trim($category) : (string)$category;
        }

        $phone = $raw['userPhone'] ?? $this->input('userPhone', $raw['phone'] ?? $this->input('phone', $raw['contact_phone'] ?? $this->input('contact_phone')));
        if ($phone !== null) {
            $merges['userPhone'] = is_string($phone) ? trim($phone) : (string)$phone;
        }

        $name = $raw['userName'] ?? $this->input('userName', $raw['name'] ?? $this->input('name', $raw['reporter_name'] ?? $this->input('reporter_name')));
        if ($name !== null) {
            $merges['userName'] = is_string($name) ? trim($name) : (string)$name;
        }

        $image = $raw['imageUrl'] ?? $this->input('imageUrl', $raw['image'] ?? $this->input('image', $raw['photo'] ?? $this->input('photo')));
        if ($image !== null) {
            $merges['imageUrl'] = $image;
        }

        $title = $raw['title'] ?? $this->input('title');
        if ($title !== null) {
            $merges['title'] = is_string($title) ? trim($title) : '';
        }

        $desc = $raw['description'] ?? $this->input('description');
        if ($desc !== null) {
            $merges['description'] = is_string($desc) ? trim($desc) : '';
        }

        $district = $raw['district'] ?? $this->input('district');
        if ($district !== null) {
            $merges['district'] = is_string($district) ? trim($district) : '';
        }

        $address = $raw['address'] ?? $this->input('address');
        if ($address !== null) {
            $merges['address'] = is_string($address) ? trim($address) : '';
        }

        if (!empty($merges)) {
            $this->merge($merges);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title'       => 'required|string|min:4|max:200',
            'description' => 'required|string|min:8',
            'district'    => 'required|string|max:100',
            'address'     => 'required|string|min:4|max:255',
            'category'    => 'nullable|string|max:100',
            'userPhone'   => 'nullable|string|max:30',
            'userName'    => 'nullable|string|max:100',
            'imageUrl'    => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if (!$this->isValidBase64Image($value)) {
                        $fail('Format foto bukti laporan tidak valid atau melebihi batas ukuran 10MB.');
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
            'title.required'       => 'Judul pengaduan kendala air wajib diisi.',
            'title.min'            => 'Judul pengaduan minimal terdiri dari 4 karakter.',
            'description.required' => 'Uraian atau deskripsi pengaduan wajib diisi.',
            'description.min'      => 'Uraian pengaduan minimal terdiri dari 8 karakter.',
            'district.required'    => 'Kecamatan lokasi kendala wajib dipilih.',
            'address.required'     => 'Alamat patokan fisik lokasi kendala wajib diisi.',
            'address.min'          => 'Alamat patokan minimal 4 karakter.',
        ];
    }
}
