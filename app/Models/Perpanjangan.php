<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Perpanjangan extends Model
{
    use HasFactory;

    protected $table = 'perpanjangans';

    protected $fillable = [
        'sewa_id',
        'user_id',
        'durasi_bulan',
        'tipe_pembayaran',
        'tanggal_mulai_baru',
        'tanggal_selesai_baru',
        'nominal_total',
        'nominal_dp',
        'dp_persen',
        'status',
        'catatan',
        'alasan_penolakan',
    ];

    protected $casts = [
        'durasi_bulan' => 'integer',
        'tanggal_mulai_baru' => 'date',
        'tanggal_selesai_baru' => 'date',
        'nominal_total' => 'decimal:2',
        'nominal_dp' => 'decimal:2',
        'dp_persen' => 'integer',
    ];

    public function sewa(): BelongsTo
    {
        return $this->belongsTo(Sewa::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function pembayarans(): HasMany
    {
        return $this->hasMany(Pembayaran::class);
    }

    public function dpPembayaran(): HasOne
    {
        return $this->hasOne(Pembayaran::class)->whereIn('jenis_pembayaran', ['DP Perpanjangan', 'Pelunasan Perpanjangan', 'Lunas Perpanjangan'])->latestOfMany();
    }

    public function tagihans(): HasMany
    {
        return $this->hasMany(Tagihan::class);
    }
}
