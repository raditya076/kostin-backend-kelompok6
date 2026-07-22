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
        $pesan = $this->filled('pesan') ? trim(strip_tags((string) $this->pesan)) : 'Halo, saya tertarik dengan kos ini dan ingin menanyakan informasi lebih lanjut.';
        $this->merge([
            'pesan' => $pesan,
        ]);
    }

    /**
     * Rules validasi.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'pesan' => ['nullable', 'string', 'max:500'],
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