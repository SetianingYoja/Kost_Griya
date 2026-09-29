<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rating extends Model
{
    use HasFactory;

    protected $table = 'ratings';

    protected $fillable = [
        'user_id',
        'kamar_id',
        'sewa_id',
        'keluhan_id',
        'jenis_rating',
        'skor',
        'komentar',
        'status',
        'balasan',
        'dibalas_pada',
        'disetujui_pada',
    ];

    protected $casts = [
        'skor' => 'integer',
        'dibalas_pada' => 'datetime',
        'disetujui_pada' => 'datetime',
    ];

    public function scopeValidForDisplay($query)
    {
        return $query
            ->where('status', 'Disetujui')
            ->whereNotNull('user_id')
            ->whereNotNull('kamar_id')
            ->whereNotNull('sewa_id')
            ->whereHas('sewa', function ($query) {
                $query->where('status', 'Aktif')
                    ->whereHas('booking', function ($bookingQuery) {
                        $bookingQuery->where('status', 'Selesai')
                            ->whereHas('pembayarans', function ($pembayaranQuery) {
                                $pembayaranQuery->where('status', 'Lunas');
                            });
                    });
            });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function keluhan(): BelongsTo
    {
        return $this->belongsTo(Keluhan::class);
    }

    public function kamar(): BelongsTo
    {
        return $this->belongsTo(Kamar::class);
    }

    public function sewa(): BelongsTo
    {
        return $this->belongsTo(Sewa::class);
    }
}
