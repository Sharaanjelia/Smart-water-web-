<?php

namespace App\Http\Requests\Meter;

use App\Http\Requests\BaseFormRequest;

class StoreMeterReadingRequest extends BaseFormRequest
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

        $meter = $raw['meter'] ?? $this->input('meter', $raw['meterNumber'] ?? $this->input('meterNumber', $raw['meter_number'] ?? $this->input('meter_number')));
        if ($meter !== null) {
            $merges['meter'] = is_string($meter) ? trim($meter) : (string)$meter;
        }

        $ocr = $raw['ocrReading'] ?? $this->input('ocrReading', $raw['reading'] ?? $this->input('reading', $raw['reading_value'] ?? $this->input('reading_value', $raw['readingValue'] ?? $this->input('readingValue'))));
        if ($ocr !== null) {
            $merges['ocrReading'] = is_numeric($ocr) ? (float)$ocr : $ocr;
        }

        $last = $raw['lastReading'] ?? $this->input('lastReading', $raw['previous_reading'] ?? $this->input('previous_reading'));
        if ($last !== null) {
            $merges['lastReading'] = is_numeric($last) ? (float)$last : $last;
        }

        $name = $raw['name'] ?? $this->input('name', $raw['customer_name'] ?? $this->input('customer_name'));
        if ($name !== null) {
            $merges['name'] = is_string($name) ? trim($name) : (string)$name;
        }

        $zone = $raw['zone'] ?? $this->input('zone');
        if ($zone !== null) {
            $merges['zone'] = is_string($zone) ? trim($zone) : (string)$zone;
        }

        $image = $raw['image'] ?? $this->input('image', $raw['photo'] ?? $this->input('photo', $raw['imageUrl'] ?? $this->input('imageUrl')));
        if ($image !== null) {
            $merges['image'] = $image;
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
            'meter'       => 'required|string|max:50',
            'ocrReading'  => 'required|numeric|min:0|max:9999999',
            'lastReading' => 'nullable|numeric|min:0|max:9999999',
            'name'        => 'nullable|string|max:100',
            'zone'        => 'nullable|string|max:100',
            'image'       => [
                'nullable',
                'string',
                function ($attribute, $value, $fail) {
                    if (!$this->isValidBase64Image($value)) {
                        $fail('Format foto angka meteran tidak valid atau ukuran melebihi batas 10MB.');
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
            'meter.required'      => 'Nomor meteran air wajib diisi.',
            'ocrReading.required' => 'Angka stand meter (m³) wajib diisi.',
            'ocrReading.numeric'  => 'Angka stand meter harus berupa nilai numerik valid.',
            'ocrReading.min'      => 'Angka stand meter tidak boleh bernilai negatif.',
        ];
    }
}
