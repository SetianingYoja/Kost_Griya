<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Tagihan extends Model
{
    use HasFactory;

    protected $table = 'tagihans';

    protected $fillable = [
        'nomor_tagihan',
        'user_id',
        'kamar_id',
        'sewa_id',
        'perpanjangan_id',
        'periode',
        'bulan_ke',
        'nominal',
        'potongan_dp',
        'total_bayar',
        'tanggal_jatuh_tempo',
        'status',
    ];

    protected $casts = [
        'nominal' => 'decimal:2',
        'potongan_dp' => 'decimal:2',
        'total_bayar' => 'decimal:2',
        'tanggal_jatuh_tempo' => 'date',
        'bulan_ke' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kamar(): BelongsTo
    {
        return $this->belongsTo(Kamar::class);
    }

    public function sewa(): BelongsTo
    {
        return $this->belongsTo(Sewa::class);
    }

    public function perpanjangan(): BelongsTo
    {
        return $this->belongsTo(Perpanjangan::class);
    }

    public function pembayarans(): HasMany
    {
        return $this->hasMany(Pembayaran::class);
    }

    public function latestPembayaran(): HasOne
    {
        return $this->hasOne(Pembayaran::class)->latestOfMany();
    }
}
