<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreBookingRequest extends FormRequest
{
    /**
     * Menentukan apakah user diizinkan untuk membuat request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk proses booking kos.
     */
    public function rules(): array
    {
        return [
            'kos_id'          => [
                'required',
                'integer',
                Rule::exists('kos', 'id')->where(function ($query) {
                    $query->where('status', 'aktif');
                })
            ],
            'tanggal_masuk'   => 'required|date|after_or_equal:today',
            'durasi_bulan'    => 'required|integer|min:1',
            'nomor_kamar'     => 'nullable|string|max:50',
            'catatan_penyewa' => 'nullable|string',
        ];
    }

    /**
     * Kustomisasi pesan validasi agar lebih informatif.
     */
    public function messages(): array
    {
        return [
            'kos_id.exists' => 'Kos yang dipilih tidak ditemukan atau sedang tidak aktif.',
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

        // Konversi string angka ke integer untuk keys tertentu
        if (isset($sanitized['kos_id'])) {
            $sanitized['kos_id'] = filter_var($sanitized['kos_id'], FILTER_VALIDATE_INT) ?: $sanitized['kos_id'];
        }
        if (isset($sanitized['durasi_bulan'])) {
            $sanitized['durasi_bulan'] = filter_var($sanitized['durasi_bulan'], FILTER_VALIDATE_INT) ?: $sanitized['durasi_bulan'];
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