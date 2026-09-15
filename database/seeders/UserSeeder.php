<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $superAdminRole = Role::where('slug', 'super-admin')->first();
        $pemilikRole = Role::where('slug', 'pemilik-kost')->first();
        $penghuniRole = Role::where('slug', 'penghuni')->first();

        // 1. Super Admin
        User::firstOrCreate(
            ['email' => 'superadmin@griyaayu.com'],
            [
                'role_id' => $superAdminRole?->id,
                'name' => 'Super Administrator',
                'phone' => '081122334455',
                'status' => 'Aktif',
                'password' => Hash::make('password123'),
            ]
        );

        // 2. Pemilik Kost
        User::firstOrCreate(
            ['email' => 'pemilik@griyaayu.com'],
            [
                'role_id' => $pemilikRole?->id,
                'name' => 'Ibu Griya Ayu (Pemilik)',
                'phone' => '081234567890',
                'status' => 'Aktif',
                'password' => Hash::make('password123'),
            ]
        );

        // 3. Demo Penghuni
        User::firstOrCreate(
            ['email' => 'penghuni@griyaayu.com'],
            [
                'role_id' => $penghuniRole?->id,
                'name' => 'Anisa Rahmawati',
                'phone' => '081298765432',
                'status' => 'Aktif',
                'password' => Hash::make('password123'),
            ]
        );
    }
}
