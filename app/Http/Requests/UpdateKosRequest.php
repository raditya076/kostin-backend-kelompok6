<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateKosRequest extends FormRequest
{
    /**
     * Menentukan apakah user diizinkan untuk membuat request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi yang berlaku untuk request ini.
     */
    public function rules(): array
    {
        return [
            'nama_kos'          => 'sometimes|required|string|max:255',
            'deskripsi'         => 'nullable|string',
            'tipe'              => 'sometimes|required|string|in:putra,putri,campur',
            'alamat'            => 'sometimes|required|string',
            'kecamatan'         => 'sometimes|required|string|max:255',
            'kota'              => 'sometimes|required|string|max:255',
            'provinsi'          => 'sometimes|required|string|max:255',
            'kode_pos'          => 'nullable|string|max:10',
            'lat'               => 'nullable|numeric|between:-90,90',
            'lng'               => 'nullable|numeric|between:-180,180',
            'harga_per_bulan'   => 'sometimes|required|numeric|min:0',
            'jumlah_kamar'      => 'sometimes|required|integer|min:1',
            'kamar_terisi'      => 'nullable|integer|min:0',
            'ada_nomor_kamar'   => 'nullable|boolean',
            'wifi'              => 'nullable|boolean',
            'ac'                => 'nullable|boolean',
            'kamar_mandi_dalam' => 'nullable|boolean',
            'parkir'            => 'nullable|boolean',
            'dapur'             => 'nullable|boolean',
            'laundry'           => 'nullable|boolean',
            'security'          => 'nullable|boolean',
            'cctv'              => 'nullable|boolean',
            'foto_utama'        => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'status'            => 'nullable|string|in:aktif,nonaktif,pending',
        ];
    }

    /**
     * Melakukan sanitasi input sebelum proses validasi dijalankan (Modul V).
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

        // Konversi input string boolean menjadi boolean asli
        $booleans = [
            'ada_nomor_kamar', 'wifi', 'ac', 'kamar_mandi_dalam',
            'parkir', 'dapur', 'laundry', 'security', 'cctv'
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