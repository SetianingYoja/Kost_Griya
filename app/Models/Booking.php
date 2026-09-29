<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    use HasFactory;

    protected $table = 'bookings';

    protected $fillable = [
        'kode_booking',
        'user_id',
        'kamar_id',
        'tanggal_mulai',
        'durasi_bulan',
        'tipe_pembayaran',
        'total_harga',
        'catatan',
        'status',
        'alasan_penolakan',
        'batas_pembayaran',
        'nominal_dp',
        'midtrans_order_id',
        'midtrans_snap_token',
        'midtrans_status',
        'midtrans_paid_at',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'total_harga' => 'decimal:2',
        'nominal_dp' => 'decimal:2',
        'durasi_bulan' => 'integer',
        'batas_pembayaran' => 'datetime',
        'midtrans_paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kamar(): BelongsTo
    {
        return $this->belongsTo(Kamar::class);
    }

    public function pembayarans(): HasMany
    {
        return $this->hasMany(Pembayaran::class);
    }

    public function latestPembayaran(): HasOne
    {
        return $this->hasOne(Pembayaran::class)->latestOfMany();
    }

    public function sewa(): HasOne
    {
        return $this->hasOne(Sewa::class);
    }
}
