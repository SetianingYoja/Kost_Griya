<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KamarFoto extends Model
{
    use HasFactory;

    protected $table = 'kamar_fotos';

    protected $fillable = [
        'kamar_id',
        'foto',
        'is_cover',
        'urutan',
    ];

    protected $casts = [
        'is_cover' => 'boolean',
        'urutan' => 'integer',
    ];

    public function kamar(): BelongsTo
    {
        return $this->belongsTo(Kamar::class);
    }

    public function getFotoUrlAttribute(): ?string
    {
        if ($this->foto && file_exists(public_path('storage/' . $this->foto))) {
            return asset('storage/' . $this->foto);
        }

        return null;
    }
}
