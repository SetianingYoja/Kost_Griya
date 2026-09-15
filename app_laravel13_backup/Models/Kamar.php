<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Kamar extends Model
{
    use HasFactory;

    protected $table = 'kamar';

    protected $fillable = [
        'tipe_kamar_id',
        'nomor_kamar',
        'lantai',
        'harga',
        'fasilitas',
        'deskripsi',
        'foto',
        'status',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'lantai' => 'integer',
    ];

    public function tipeKamar(): BelongsTo
    {
        return $this->belongsTo(TipeKamar::class, 'tipe_kamar_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function sewas(): HasMany
    {
        return $this->hasMany(Sewa::class);
    }

    public function currentSewa(): HasOne
    {
        return $this->hasOne(Sewa::class)->where('status', 'Aktif')->latestOfMany();
    }

    public function keluhans(): HasMany
    {
        return $this->hasMany(Keluhan::class);
    }

    public function scopeTersedia($query)
    {
        return $query->where('status', 'Tersedia');
    }

    public function getFotoUrlAttribute(): string
    {
        if ($this->foto && file_exists(public_path('storage/' . $this->foto))) {
            return asset('storage/' . $this->foto);
        }
        return asset('images/kamar-default.svg');
    }
}
