<?php

namespace App\Models;

use Carbon\Carbon;
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

    public function scopeSedangDiproses($query)
    {
        return $query->where('status', 'Sedang Diproses');
    }

    /**
     * Memeriksa apakah kamar dapat dibooking (status 'Tersedia' dan tidak ada sewa aktif atau booking dalam proses).
     */
    public function isAvailableForBooking(): bool
    {
        if ($this->status !== 'Tersedia') {
            return false;
        }

        $hasActiveSewa = $this->sewas()->where('status', 'Aktif')->exists();
        if ($hasActiveSewa) {
            return false;
        }

        $hasInProcessBooking = $this->bookings()
            ->whereIn('status', ['Menunggu Pembayaran', 'Menunggu Validasi', 'Aktif'])
            ->where(function ($q) {
                $q->whereNull('batas_pembayaran')
                    ->orWhere('batas_pembayaran', '>', Carbon::now());
            })
            ->exists();

        return ! $hasInProcessBooking;
    }

    /**
     * Mengembalikan status kamar ke 'Tersedia' hanya jika tidak ada sewa aktif,
     * tidak ada pembayaran yang sah/menunggu validasi, dan tidak ada booking aktif lainnya.
     */
    public function releaseIfAvailable(?int $excludeBookingId = null): bool
    {
        // 1. Cek apakah ada sewa aktif untuk kamar ini
        $hasActiveSewa = $this->sewas()
            ->where('status', 'Aktif')
            ->exists();

        if ($hasActiveSewa) {
            if ($this->status !== 'Terisi') {
                $this->status = 'Terisi';
                $this->save();
            }

            return false;
        }

        // 2. Cek apakah ada pembayaran valid / menunggu validasi untuk kamar ini
        $hasValidPayment = Pembayaran::whereHas('booking', function ($q) {
            $q->where('kamar_id', $this->id);
        })
            ->whereIn('status', ['Menunggu Validasi', 'Lunas'])
            ->exists();

        if ($hasValidPayment) {
            return false;
        }

        // 3. Cek apakah ada booking lain yang masih aktif atau menunggu pembayaran belum kadaluarsa
        $otherActiveBooking = $this->bookings()
            ->when($excludeBookingId, fn ($q) => $q->where('id', '!=', $excludeBookingId))
            ->whereIn('status', ['Menunggu Pembayaran', 'Menunggu Validasi', 'Aktif'])
            ->where(function ($q) {
                $q->whereNull('batas_pembayaran')
                    ->orWhere('batas_pembayaran', '>', Carbon::now());
            })
            ->exists();

        if ($otherActiveBooking) {
            if ($this->status !== 'Sedang Diproses') {
                $this->status = 'Sedang Diproses';
                $this->save();
            }

            return false;
        }

        // 4. Jika kamar bukan dalam status maintenance manual ('Tidak tersedia'), kembalikan ke 'Tersedia'
        if ($this->status !== 'Tidak tersedia') {
            $this->status = 'Tersedia';
            $this->save();

            return true;
        }

        return false;
    }

    public function getFotoUrlAttribute(): string
    {
        if ($this->foto && file_exists(public_path('storage/'.$this->foto))) {
            return asset('storage/'.$this->foto);
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

            return $primary ? [asset('storage/'.$primary['foto'])] : [asset('images/kamar-default.svg')];
        }

        return $images
            ->map(fn ($item) => $item->foto_url ?: null)
            ->filter()
            ->values()
            ->all();
    }
}
