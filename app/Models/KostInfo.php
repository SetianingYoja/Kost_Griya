<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KostInfo extends Model
{
    use HasFactory;

    protected $table = 'kost_info';

    protected $fillable = [
        'nama_kost',
        'tagline',
        'deskripsi',
        'alamat',
        'no_telepon',
        'email',
        'bank_nama',
        'bank_rekening',
        'bank_atas_nama',
        'aturan_kost',
    ];
}
