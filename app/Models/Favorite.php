<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Favorite extends Model
{
    protected $table = 'favorites';

    /**
     * Konfigurasi timestamps. 
     * Karena migrasi tabel favorites hanya menyediakan kolom created_at,
     * kita set UPDATED_AT ke null agar Laravel tidak mencoba menulis ke kolom updated_at.
     */
    public $timestamps = true;
    const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'kos_id',
    ];

    /**
     * Relasi ke User (pencari yang memfavoritkan).
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke Kos yang difavoritkan.
     */
    public function kos(): BelongsTo
    {
        return $this->belongsTo(Kos::class, 'kos_id');
    }
}