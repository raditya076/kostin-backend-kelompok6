<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreChatRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Sanitasi input sebelum validasi.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('pesan')) {
            $this->merge([
                'pesan' => trim(strip_tags((string) $this->pesan)),
            ]);
        }
    }

    /**
     * Rules validasi.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'pesan' => ['required', 'string', 'min:5', 'max:500'],
        ];
    }

    /**
     * Pesan kesalahan validasi kustom.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'pesan.required' => 'Pesan wajib diisi.',
            'pesan.string'   => 'Pesan harus berupa teks.',
            'pesan.min'      => 'Pesan minimal berisi 5 karakter.',
            'pesan.max'      => 'Pesan maksimal 500 karakter.',
        ];
    }
}