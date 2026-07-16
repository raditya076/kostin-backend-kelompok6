<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'kos_id',
    'penyewa_id',
    'nomor_kamar',
    'tanggal_masuk',
    'durasi_bulan',
    'tanggal_keluar',
    'harga_per_bulan',
    'total_harga',
    'snap_token',
    'midtrans_order_id',
    'metode_pembayaran',
    'payment_type',
    'midtrans_status',
    'tanggal_bayar',
    'status',
    'catatan_penyewa',
    'catatan_pemilik',
])]
class Booking extends Model
{
    protected $table = 'bookings';

    /**
     * Konfigurasi tipe data casting atribut.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_masuk'  => 'date',
            'tanggal_keluar' => 'date',
            'tanggal_bayar'  => 'datetime',
            'durasi_bulan'   => 'integer',
            'harga_per_bulan'=> 'decimal:2',
            'total_harga'    => 'decimal:2',
        ];
    }

    /**
     * Relasi ke Kos.
     */
    public function kos(): BelongsTo
    {
        return $this->belongsTo(Kos::class, 'kos_id');
    }

    /**
     * Relasi ke User (Penyewa / Pencari).
     */
    public function penyewa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'penyewa_id');
    }
}