<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Sewa extends Model
{
    use HasFactory;

    protected $table = 'sewas';

    protected $fillable = [
        'user_id',
        'kamar_id',
        'booking_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'status',
        'harga_per_bulan',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'harga_per_bulan' => 'decimal:2',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kamar(): BelongsTo
    {
        return $this->belongsTo(Kamar::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function tagihans(): HasMany
    {
        return $this->hasMany(Tagihan::class);
    }

    public function perpanjangans(): HasMany
    {
        return $this->hasMany(Perpanjangan::class);
    }

    public function getSisaHariAttribute(): int
    {
        $today = Carbon::today();
        if ($this->tanggal_selesai->isPast()) {
            return 0;
        }
        return $today->diffInDays($this->tanggal_selesai, false);
    }
}
