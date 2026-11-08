<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $legacySuperAdmin = Role::where('slug', 'superadmin')->first();
        if ($legacySuperAdmin && ! Role::where('slug', 'super-admin')->exists()) {
            $legacySuperAdmin->slug = 'super-admin';
            $legacySuperAdmin->save();
        }

        // 1. Buat / pastikan semua Roles ada
        $superAdmin = Role::firstOrCreate(
            ['slug' => 'super-admin'],
            ['name' => 'Super Admin', 'description' => 'Pengelola sistem tingkat penuh, user, role, dan permission.']
        );

        // Role pengelola: mengelola seluruh operasional kost
        $pengelola = Role::firstOrCreate(
            ['slug' => 'pengelola'],
            ['name' => 'Pengelola', 'description' => 'Pengelola operasional kost: kamar, booking, pembayaran, tagihan, keluhan, perpanjangan, dan laporan operasional.']
        );

        // Role pemilik-kost: read-only, hanya memantau data dan laporan
        $pemilikKost = Role::firstOrCreate(
            ['slug' => 'pemilik-kost'],
            ['name' => 'Pemilik Kost', 'description' => 'Pemilik kost: akses read-only untuk memantau data, laporan, dan statistik operasional.']
        );

        $penghuni = Role::firstOrCreate(
            ['slug' => 'penghuni'],
            ['name' => 'Penghuni', 'description' => 'Penyewa kamar kost yang memiliki akses booking, pembayaran, tagihan, dan keluhan.']
        );

        // 2. Buat Permissions
        $permissions = [
            // User Management (Super Admin only)
            ['name' => 'Kelola Pengguna', 'slug' => 'manage-users', 'module' => 'User Management', 'description' => 'Membuat, mengedit, mengubah status dan menghapus user.'],
            ['name' => 'Kelola Peran', 'slug' => 'manage-roles', 'module' => 'Role Management', 'description' => 'Mengelola peran dalam sistem.'],
            ['name' => 'Kelola Izin', 'slug' => 'manage-permissions', 'module' => 'Permission Management', 'description' => 'Mengelola hak akses dan permission.'],
            ['name' => 'Konfigurasi Sistem', 'slug' => 'manage-settings', 'module' => 'System', 'description' => 'Mengelola konfigurasi sistem.'],

            // Kamar
            ['name' => 'Kelola Kamar', 'slug' => 'manage-kamar', 'module' => 'Kamar', 'description' => 'CRUD data kamar dan tipe kamar.'],
            ['name' => 'Lihat Kamar', 'slug' => 'view-kamar', 'module' => 'Kamar', 'description' => 'Melihat daftar dan detail kamar.'],

            // Penghuni
            ['name' => 'Kelola Data Penghuni', 'slug' => 'manage-penghuni', 'module' => 'Penghuni', 'description' => 'Melihat seluruh data penyewa kamar.'],
            ['name' => 'Lihat Data Sendiri', 'slug' => 'view-self-profile', 'module' => 'Penghuni', 'description' => 'Melihat profil dan data sewa sendiri.'],
            // Permission baru: Pemilik Kost boleh melihat daftar penghuni (read-only, tanpa edit)
            ['name' => 'Lihat Data Penghuni', 'slug' => 'view-penghuni-readonly', 'module' => 'Penghuni', 'description' => 'Melihat daftar dan detail penghuni secara read-only (tanpa edit).'],

            // Booking
            ['name' => 'Validasi Booking', 'slug' => 'validate-booking', 'module' => 'Booking', 'description' => 'Menyetujui atau menolak pesanan kamar.'],
            ['name' => 'Buat Booking', 'slug' => 'create-booking', 'module' => 'Booking', 'description' => 'Melakukan pemesanan kamar.'],

            // Pembayaran
            ['name' => 'Validasi Pembayaran', 'slug' => 'validate-pembayaran', 'module' => 'Pembayaran', 'description' => 'Memverifikasi bukti transfer dan melunaskan tagihan.'],
            ['name' => 'Upload Pembayaran', 'slug' => 'create-pembayaran', 'module' => 'Pembayaran', 'description' => 'Mengunggah bukti pembayaran transfer.'],

            // Tagihan
            ['name' => 'Kelola Tagihan', 'slug' => 'manage-tagihan', 'module' => 'Tagihan', 'description' => 'Membuat dan memantau status tagihan bulanan.'],
            ['name' => 'Lihat Tagihan Sendiri', 'slug' => 'view-self-tagihan', 'module' => 'Tagihan', 'description' => 'Melihat invoice dan kewajiban bayar sendiri.'],

            // Keluhan & Rating
            ['name' => 'Tanggapi Keluhan', 'slug' => 'manage-keluhan', 'module' => 'Keluhan', 'description' => 'Memproses dan memberi respon pada keluhan penghuni.'],
            ['name' => 'Kirim Keluhan', 'slug' => 'create-keluhan', 'module' => 'Keluhan', 'description' => 'Mengirim laporan komplain atau keluhan.'],
            ['name' => 'Moderasi Rating', 'slug' => 'manage-rating', 'module' => 'Rating', 'description' => 'Melihat dan memoderasi rating & ulasan dari penghuni.'],
            ['name' => 'Beri Rating', 'slug' => 'create-rating', 'module' => 'Rating', 'description' => 'Memberi penilaian kost dan penanganan keluhan.'],

            // Laporan
            ['name' => 'Lihat Laporan Operasional', 'slug' => 'view-laporan-operasional', 'module' => 'Laporan', 'description' => 'Melihat laporan pendapatan, okupansi, dan keluhan.'],
            ['name' => 'Lihat Laporan Sistem', 'slug' => 'view-laporan-sistem', 'module' => 'Laporan', 'description' => 'Melihat rekap data global sistem.'],
        ];

        $permissionModels = [];
        foreach ($permissions as $p) {
            $permissionModels[$p['slug']] = Permission::firstOrCreate(
                ['slug' => $p['slug']],
                $p
            );
        }

        // 3. Kaitkan Permissions ke Roles

        // Super Admin: selalu memiliki seluruh permissions yang ada di sistem (baik yang didefinisikan di seeder maupun yang sudah ada di database)
        $allPermissionIds = Permission::pluck('id')->all();
        $superAdmin->permissions()->sync($allPermissionIds);

        // Pengelola: semua permissions operasional (kecuali user/role/system management)
        $pengelolaPermissions = [
            'manage-kamar',
            'view-kamar',
            'manage-penghuni',
            'view-penghuni-readonly',
            'validate-booking',
            'validate-pembayaran',
            'manage-tagihan',
            'manage-keluhan',
            'manage-rating',
            'view-laporan-operasional',
        ];
        $pengelolaIds = array_filter(array_map(fn ($slug) => $permissionModels[$slug]->id ?? null, $pengelolaPermissions));
        $pengelola->permissions()->sync($pengelolaIds);

        // Pemilik Kost: read-only — hanya lihat data dan laporan, tidak bisa ubah apapun
        $pemilikKostPermissions = [
            'view-kamar',
            'view-penghuni-readonly',
            'view-laporan-operasional',
        ];
        $pemilikKostIds = array_filter(array_map(fn ($slug) => $permissionModels[$slug]->id ?? null, $pemilikKostPermissions));
        $pemilikKost->permissions()->sync($pemilikKostIds);

        // Penghuni
        $penghuniPermissions = [
            'view-kamar',
            'view-self-profile',
            'create-booking',
            'create-pembayaran',
            'view-self-tagihan',
            'create-keluhan',
            'create-rating',
        ];
        $penghuniIds = array_filter(array_map(fn ($slug) => $permissionModels[$slug]->id ?? null, $penghuniPermissions));
        $penghuni->permissions()->sync($penghuniIds);
    }
}
