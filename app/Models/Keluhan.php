<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Keluhan extends Model
{
    use HasFactory;

    protected $table = 'keluhans';

    protected $fillable = [
        'kode_keluhan',
        'user_id',
        'kamar_id',
        'judul',
        'isi',
        'foto',
        'status',
        'tanggapan',
        'selesai_pada',
    ];

    protected $casts = [
        'selesai_pada' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kamar(): BelongsTo
    {
        return $this->belongsTo(Kamar::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    public function rating(): HasOne
    {
        return $this->hasOne(Rating::class)->where('jenis_rating', 'Penanganan Keluhan');
    }

    public function getFotoUrlAttribute(): ?string
    {
        if ($this->foto && file_exists(public_path('storage/' . $this->foto))) {
            return asset('storage/' . $this->foto);
        }
        return null;
    }
}
