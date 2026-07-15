<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CompareKosRequest extends FormRequest
{
    /**
     * Menentukan apakah user diizinkan untuk membuat request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi perbandingan kos (wajib berupa array, minimal 2 dan maksimal 3 item).
     */
    public function rules(): array
    {
        return [
            'ids'   => 'required|array|min:2|max:3',
            'ids.*' => 'required|integer|exists:kos,id',
        ];
    }

    /**
     * Sanitasi parameter masukan sebelum proses validasi.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('ids') && is_array($this->ids)) {
            $sanitizedIds = array_map(function ($id) {
                return filter_var($id, FILTER_VALIDATE_INT) ?: $id;
            }, $this->ids);

            $this->merge([
                'ids' => $sanitizedIds,
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