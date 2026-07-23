<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Menentukan apakah user diizinkan untuk membuat request ini.
     */
    public function authorize(): bool
    {
        return true; // Ubah ke true agar request dapat diproses
    }

    /**
     * Aturan validasi yang berlaku untuk request ini.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama'                  => 'required|string|max:255',
            'no_hp'                 => 'nullable|string|max:20',
            'nama_bank'             => 'nullable|string|max:100',
            'nomor_rekening'        => 'nullable|string|max:50',
            'nama_pemilik_rekening' => 'nullable|string|max:255',
            'jenis_kelamin'         => 'nullable|string|max:50',
            'tanggal_lahir'         => 'nullable|string|max:50',
            'alamat'                => 'nullable|string|max:500',
            'nik'                   => 'nullable|string|max:30',
        ];
    }

    /**
     * Melakukan sanitasi input sebelum proses validasi dijalankan (Modul V).
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'nama'                  => $this->nama ? strip_tags(trim($this->nama)) : null,
            'no_hp'                 => $this->no_hp ? strip_tags(trim($this->no_hp)) : null,
            'nama_bank'             => $this->nama_bank ? strip_tags(trim($this->nama_bank)) : null,
            'nomor_rekening'        => $this->nomor_rekening ? strip_tags(trim($this->nomor_rekening)) : null,
            'nama_pemilik_rekening' => $this->nama_pemilik_rekening ? strip_tags(trim($this->nama_pemilik_rekening)) : null,
            'jenis_kelamin'         => $this->jenis_kelamin ? strip_tags(trim($this->jenis_kelamin)) : null,
            'tanggal_lahir'         => $this->tanggal_lahir ? strip_tags(trim($this->tanggal_lahir)) : null,
            'alamat'                => $this->alamat ? strip_tags(trim($this->alamat)) : null,
            'nik'                   => $this->nik ? strip_tags(trim($this->nik)) : null,
        ]);
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