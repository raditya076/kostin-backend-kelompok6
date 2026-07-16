<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreReviewRequest extends FormRequest
{
    /**
     * Menentukan apakah user diizinkan untuk membuat request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk ulasan dan rating kos.
     */
    public function rules(): array
    {
        return [
            'rating'     => 'required|integer|between:1,5',
            'judul'      => 'nullable|string|max:120',
            'isi_ulasan' => 'required|string',
        ];
    }

    /**
     * Sanitasi parameter masukan sebelum proses validasi (Modul V).
     */
    protected function prepareForValidation(): void
    {
        $sanitized = [];
        foreach ($this->all() as $key => $value) {
            if (is_string($value)) {
                $sanitized[$key] = trim(strip_tags($value));
            } else {
                $sanitized[$key] = $value;
            }
        }

        if (isset($sanitized['rating'])) {
            $sanitized['rating'] = filter_var($sanitized['rating'], FILTER_VALIDATE_INT) ?: $sanitized['rating'];
        }

        $this->merge($sanitized);
    }

    /**
     * Kustomisasi format response ketika validasi gagal sesuai standar BaseController.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => $validator->errors()->first(),
        ], 422));
    }
}