<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Tahap 1 fondasi role:
     * 1. Tambah role `pengelola` (pengelola operasional kost).
     * 2. Perbarui deskripsi `pemilik-kost` menjadi read-only monitoring.
     * 3. Migrasikan user operasional lama (slug pemilik-kost) ke role pengelola.
     *
     * Catatan: permissions untuk tiap role diatur di RolePermissionSeeder.
     */
    public function up(): void
    {
        // 1. Tambah role pengelola jika belum ada
        $existingPengelola = DB::table('roles')->where('slug', 'pengelola')->first();
        if (! $existingPengelola) {
            DB::table('roles')->insert([
                'name'        => 'Pengelola',
                'slug'        => 'pengelola',
                'description' => 'Pengelola operasional kost: kamar, booking, pembayaran, tagihan, keluhan, perpanjangan, dan laporan operasional.',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);
        }

        // 2. Perbarui deskripsi role pemilik-kost agar mencerminkan fungsi read-only baru
        DB::table('roles')
            ->where('slug', 'pemilik-kost')
            ->update([
                'description' => 'Pemilik kost: akses read-only untuk memantau data, laporan, dan statistik operasional.',
                'updated_at'  => now(),
            ]);

        // 3. Migrasikan akun operasional lama (user ID 2) dari pemilik-kost ke pengelola.
        //    Secara spesifik hanya menargetkan akun operasional historis ID 2 agar akun lain tidak terpengaruh.
        $pengelolaRole = DB::table('roles')->where('slug', 'pengelola')->first();
        $pemilikKostRole = DB::table('roles')->where('slug', 'pemilik-kost')->first();

        if ($pengelolaRole && $pemilikKostRole) {
            DB::table('users')
                ->where('id', 2)
                ->where('role_id', $pemilikKostRole->id)
                ->update([
                    'role_id'    => $pengelolaRole->id,
                    'updated_at' => now(),
                ]);
        }
    }

    public function down(): void
    {
        // Kembalikan deskripsi pemilik-kost ke versi lama
        DB::table('roles')
            ->where('slug', 'pemilik-kost')
            ->update([
                'description' => 'Pengelola operasional kost, kamar, booking, pembayaran, dan keluhan.',
                'updated_at'  => now(),
            ]);

        // HANYA kembalikan akun operasional historis (ID 2) yang sebelumnya dimigrasikan dari pemilik-kost.
        // JANGAN mengubah role akun Pengelola lain yang mungkin dibuat setelah migrasi.
        $pengelolaRole   = DB::table('roles')->where('slug', 'pengelola')->first();
        $pemilikKostRole = DB::table('roles')->where('slug', 'pemilik-kost')->first();

        if ($pengelolaRole && $pemilikKostRole) {
            DB::table('users')
                ->where('id', 2)
                ->where('role_id', $pengelolaRole->id)
                ->update([
                    'role_id'    => $pemilikKostRole->id,
                    'updated_at' => now(),
                ]);
        }

        // Hapus role pengelola hanya jika tidak ada user yang masih memakainya
        if ($pengelolaRole) {
            $hasUsers = DB::table('users')->where('role_id', $pengelolaRole->id)->exists();
            if (! $hasUsers) {
                DB::table('roles')->where('id', $pengelolaRole->id)->delete();
            }
        }
    }
};
