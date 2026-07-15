<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'kos_id',
    'user_id',
    'rating',
    'judul',
    'isi_ulasan'
])]
class Review extends Model
{
    protected $table = 'reviews';

    /**
     * Relasi kembali ke Kos.
     */
    public function kos(): BelongsTo
    {
        return $this->belongsTo(Kos::class, 'kos_id');
    }

    /**
     * Relasi ke User (pembuat ulasan).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}