<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PembagianDana extends Model
{
    protected $table = 'pembagian_dana';

    /**
     * Atribut yang dapat diisi massal (mass assignable).
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'booking_id',
        'pemilik_id',
        'total_transaksi',
        'persen_platform',
        'biaya_platform',
        'biaya_gateway',
        'jatah_pemilik',
        'status_disbursement',
        'catatan',
    ];

    /**
     * Konfigurasi tipe data casting atribut.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'total_transaksi' => 'decimal:2',
            'persen_platform' => 'decimal:2',
            'biaya_platform'  => 'decimal:2',
            'biaya_gateway'   => 'decimal:2',
            'jatah_pemilik'   => 'decimal:2',
        ];
    }

    /**
     * Relasi ke Booking.
     *
     * @return BelongsTo
     */
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    /**
     * Relasi ke Pemilik (User).
     *
     * @return BelongsTo
     */
    public function pemilik(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pemilik_id');
    }
}