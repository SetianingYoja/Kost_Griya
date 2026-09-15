<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TipeKamar extends Model
{
    use HasFactory;

    protected $table = 'tipe_kamar';

    protected $fillable = [
        'nama_tipe',
        'slug',
        'harga_bulanan',
        'fasilitas_dasar',
        'deskripsi',
    ];

    protected $casts = [
        'harga_bulanan' => 'decimal:2',
    ];

    public function kamars(): HasMany
    {
        return $this->hasMany(Kamar::class, 'tipe_kamar_id');
    }
}
