<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'pemilik_id',
    'nama_kos',
    'deskripsi',
    'tipe',
    'alamat',
    'kecamatan',
    'kota',
    'provinsi',
    'kode_pos',
    'lat',
    'lng',
    'harga_per_bulan',
    'jumlah_kamar',
    'kamar_terisi',
    'ada_nomor_kamar',
    'wifi',
    'ac',
    'kamar_mandi_dalam',
    'parkir',
    'dapur',
    'laundry',
    'security',
    'cctv',
    'foto_utama',
    'status'
])]
class Kos extends Model
{
    protected $table = 'kos';

    protected $appends = ['foto_utama_url', 'sisa_kamar', 'kamar_tersedia', 'rating'];

    public function getFotoUtamaUrlAttribute(): ?string
    {
        if (!$this->foto_utama) {
            return null;
        }
        if (str_starts_with($this->foto_utama, 'http://') || str_starts_with($this->foto_utama, 'https://')) {
            return $this->foto_utama;
        }
        return url(\Illuminate\Support\Facades\Storage::url($this->foto_utama));
    }

    public function getSisaKamarAttribute(): int
    {
        return max(0, (int)($this->jumlah_kamar ?? 0) - (int)($this->kamar_terisi ?? 0));
    }

    public function getKamarTersediaAttribute(): int
    {
        return $this->getSisaKamarAttribute();
    }

    public function getRatingAttribute(): float
    {
        if ($this->relationLoaded('reviews')) {
            $avg = $this->reviews->avg('rating');
            return $avg ? round((float)$avg, 1) : 0.0;
        }
        $avg = $this->reviews()->avg('rating');
        return $avg ? round((float)$avg, 1) : 0.0;
    }

    /**
     * Konfigurasi tipe data casting atribut.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ada_nomor_kamar'   => 'boolean',
            'wifi'              => 'boolean',
            'ac'                => 'boolean',
            'kamar_mandi_dalam' => 'boolean',
            'parkir'            => 'boolean',
            'dapur'             => 'boolean',
            'laundry'           => 'boolean',
            'security'          => 'boolean',
            'cctv'              => 'boolean',
            'harga_per_bulan'   => 'decimal:2',
            'lat'               => 'decimal:8',
            'lng'               => 'decimal:8',
        ];
    }

    /**
     * Relasi ke User (Pemilik).
     */
    public function pemilik(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pemilik_id');
    }

    /**
     * Relasi ke Galeri Foto Kos (nama method lama).
     */
    public function fotos(): HasMany
    {
        return $this->hasMany(KosFoto::class, 'kos_id');
    }

    /**
     * Relasi ke Galeri Foto Kos (nama method baru sesuai request asdos).
     */
    public function kosFoto(): HasMany
    {
        return $this->hasMany(KosFoto::class, 'kos_id');
    }

    /**
     * Relasi ke Ulasan (Reviews).
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'kos_id');
    }
}