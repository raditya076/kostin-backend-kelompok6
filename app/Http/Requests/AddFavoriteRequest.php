<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class AddFavoriteRequest extends FormRequest
{
    /**
     * Menentukan apakah user diizinkan untuk membuat request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk menambahkan kos favorit.
     */
    public function rules(): array
    {
        return [
            'kos_id' => 'required|integer|exists:kos,id',
        ];
    }

    /**
     * Sanitasi parameter masukan sebelum proses validasi.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('kos_id')) {
            $this->merge([
                'kos_id' => filter_var($this->kos_id, FILTER_VALIDATE_INT) ?: $this->kos_id,
            ]);
        }
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