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

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    public function galleryImages(): HasMany
    {
        return $this->hasMany(KamarFoto::class)->orderBy('urutan')->orderBy('id');
    }

    public function coverImage(): HasOne
    {
        return $this->hasOne(KamarFoto::class)->where('is_cover', true)->orderBy('urutan')->orderBy('id');
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

        $cover = $this->coverImage()->first();
        if ($cover && $cover->foto_url) {
            return $cover->foto_url;
        }

        return asset('images/kamar-default.svg');
    }

    public function getGalleryFotoUrlsAttribute(): array
    {
        $images = $this->galleryImages()->get();

        if ($images->isEmpty()) {
            $primary = $this->foto ? ['foto' => $this->foto] : [];
            return $primary ? [asset('storage/' . $primary['foto'])] : [asset('images/kamar-default.svg')];
        }

        return $images
            ->map(fn ($item) => $item->foto_url ?: null)
            ->filter()
            ->values()
            ->all();
    }
}
