<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'role_id',
        'name',
        'email',
        'phone',
        'status',
        'avatar',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function sewas(): HasMany
    {
        return $this->hasMany(Sewa::class);
    }

    public function activeSewa(): HasOne
    {
        return $this->hasOne(Sewa::class)->where('status', 'Aktif')->latestOfMany();
    }

    public function tagihans(): HasMany
    {
        return $this->hasMany(Tagihan::class);
    }

    public function pembayarans(): HasMany
    {
        return $this->hasMany(Pembayaran::class);
    }

    public function perpanjangans(): HasMany
    {
        return $this->hasMany(Perpanjangan::class);
    }

    public function keluhans(): HasMany
    {
        return $this->hasMany(Keluhan::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(Rating::class);
    }

    public function riwayatAktivitas(): HasMany
    {
        return $this->hasMany(RiwayatAktivitas::class);
    }

    // Role checks
    public function isSuperAdmin(): bool
    {
        return $this->role && $this->role->slug === 'super-admin';
    }

    public function isPemilik(): bool
    {
        return $this->role && $this->role->slug === 'pemilik-kost';
    }

    public function isPenghuni(): bool
    {
        return $this->role && $this->role->slug === 'penghuni';
    }

    public function hasRole(string $roleSlug): bool
    {
        return $this->role && $this->role->slug === $roleSlug;
    }

    public function hasPermission(string $permissionSlug): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if (!$this->role) {
            return false;
        }

        return $this->role->permissions->contains('slug', $permissionSlug);
    }
}
