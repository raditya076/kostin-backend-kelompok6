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

    protected $appends = ['nama_file_url'];

    public function getNamaFileUrlAttribute(): ?string
    {
        if (!$this->nama_file) {
            return null;
        }
        if (str_starts_with($this->nama_file, 'http://') || str_starts_with($this->nama_file, 'https://')) {
            return $this->nama_file;
        }
        return url(\Illuminate\Support\Facades\Storage::url($this->nama_file));
    }

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