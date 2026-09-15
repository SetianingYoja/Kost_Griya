<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatAktivitas extends Model
{
    use HasFactory;

    protected $table = 'riwayat_aktivitas';

    protected $fillable = [
        'user_id',
        'judul',
        'deskripsi',
        'tipe',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function catat(int $userId, string $judul, ?string $deskripsi = null, string $tipe = 'info'): self
    {
        return self::create([
            'user_id' => $userId,
            'judul' => $judul,
            'deskripsi' => $deskripsi,
            'tipe' => $tipe,
        ]);
    }
}
