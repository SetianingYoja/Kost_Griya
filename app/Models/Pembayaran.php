<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Pembayaran extends Model
{
    use HasFactory;

    protected $table = 'pembayarans';

    protected $fillable = [
        'kode_pembayaran',
        'user_id',
        'booking_id',
        'tagihan_id',
        'perpanjangan_id',
        'jenis_pembayaran',
        'nominal',
        'metode_pembayaran',
        'nama_pengirim',
        'bank_pengirim',
        'bukti_pembayaran',
        'tanggal_bayar',
        'status',
        'catatan_penghuni',
        'catatan_pemilik',
        'diverifikasi_pada',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'tanggal_bayar' => 'date',
        'diverifikasi_pada' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function tagihan(): BelongsTo
    {
        return $this->belongsTo(Tagihan::class);
    }

    public function perpanjangan(): BelongsTo
    {
        return $this->belongsTo(Perpanjangan::class);
    }

    public function getBuktiUrlAttribute(): ?string
    {
        if ($this->bukti_pembayaran && file_exists(public_path('storage/' . $this->bukti_pembayaran))) {
            return asset('storage/' . $this->bukti_pembayaran);
        }
        return null;
    }
}
