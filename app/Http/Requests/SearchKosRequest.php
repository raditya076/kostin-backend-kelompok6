<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SearchKosRequest extends FormRequest
{
    /**
     * Menentukan apakah user diizinkan untuk membuat request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi pencarian kos.
     */
    public function rules(): array
    {
        return [
            'kota'              => 'nullable|string|max:255',
            'harga_min'         => 'nullable|numeric|min:0',
            'harga_max'         => 'nullable|numeric|min:0',
            'tipe'              => 'nullable|string|in:putra,putri,campur',
            'wifi'              => 'nullable|boolean',
            'ac'                => 'nullable|boolean',
            'kamar_mandi_dalam' => 'nullable|boolean',
            'parkir'            => 'nullable|boolean',
            'dapur'             => 'nullable|boolean',
            'laundry'           => 'nullable|boolean',
            'security'          => 'nullable|boolean',
            'cctv'              => 'nullable|boolean',
            'user_lat'          => 'nullable|numeric|between:-90,90',
            'user_lng'          => 'nullable|numeric|between:-180,180',
            'radius_km'         => 'nullable|numeric|min:0.5|max:100',
        ];
    }

    /**
     * Sanitasi parameter teks menggunakan trim & strip_tags dan konversi tipe data boolean.
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

        // Konversi string "true"/"false" atau "1"/"0" dari query parameter menjadi tipe data boolean asli
        $booleans = [
            'wifi', 'ac', 'kamar_mandi_dalam', 'parkir', 
            'dapur', 'laundry', 'security', 'cctv'
        ];

        foreach ($booleans as $field) {
            if ($this->has($field)) {
                $sanitized[$field] = filter_var($sanitized[$field], FILTER_VALIDATE_BOOLEAN);
            }
        }

        $this->merge($sanitized);
    }

    /**
     * Kustomisasi format response ketika validasi gagal agar mengikuti standard BaseController.
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => $validator->errors()->first(),
        ], 422));
    }
}