<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

#[Fillable([
    'nama',
    'email',
    'password',
    'no_hp',
    'nama_bank',
    'nomor_rekening',
    'nama_pemilik_rekening',
    'jenis_kelamin',
    'tanggal_lahir',
    'alamat',
    'nik',
    'foto_ktp',
    'role',
    'foto_profil',
    'status'
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Accessor untuk URL foto profil dan KTP.
     */
    protected $appends = ['foto_profil_url', 'foto_ktp_url'];

    public function getFotoProfilUrlAttribute(): ?string
    {
        if (!$this->foto_profil) {
            return null;
        }
        if (str_starts_with($this->foto_profil, 'http://') || str_starts_with($this->foto_profil, 'https://')) {
            return $this->foto_profil;
        }
        return asset('storage/' . $this->foto_profil);
    }

    public function getFotoKtpUrlAttribute(): ?string
    {
        if (!$this->foto_ktp) {
            return null;
        }
        if (str_starts_with($this->foto_ktp, 'http://') || str_starts_with($this->foto_ktp, 'https://')) {
            return $this->foto_ktp;
        }
        return asset('storage/' . $this->foto_ktp);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}