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
        'keluhan_id',
        'jenis_rating',
        'skor',
        'komentar',
    ];

    protected $casts = [
        'skor' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function keluhan(): BelongsTo
    {
        return $this->belongsTo(Keluhan::class);
    }
}
