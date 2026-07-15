<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'kos_id',
    'nama_file',
    'urutan'
])]
class KosFoto extends Model
{
    protected $table = 'kos_foto';

    public $timestamps = true;
    const UPDATED_AT = null;

    /**
     * Relasi kembali ke Kos.
     */
    public function kos(): BelongsTo
    {
        return $this->belongsTo(Kos::class, 'kos_id');
    }
}