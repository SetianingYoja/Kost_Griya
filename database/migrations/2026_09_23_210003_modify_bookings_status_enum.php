<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Peta data lama berstatus 'Disetujui' ke 'Menunggu Pembayaran' sebelum ENUM diubah
        DB::statement("UPDATE bookings SET status = 'Menunggu Pembayaran' WHERE status = 'Disetujui'");

        // 2. Ubah definisi ENUM status pada tabel bookings (menambah 'Aktif', menghapus 'Disetujui')
        DB::statement("ALTER TABLE bookings MODIFY status ENUM(
            'Menunggu Pembayaran',
            'Menunggu Validasi',
            'Aktif',
            'Ditolak',
            'Kadaluarsa',
            'Dibatalkan',
            'Selesai'
        ) NOT NULL DEFAULT 'Menunggu Pembayaran'");

        // 3. Backfill booking berstatus 'Selesai' yang sewa-nya masih 'Aktif' menjadi status 'Aktif'
        DB::statement("UPDATE bookings SET status = 'Aktif' WHERE status = 'Selesai' AND id IN (
            SELECT booking_id FROM sewas WHERE status = 'Aktif' AND booking_id IS NOT NULL
        )");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // SYARAT UTAMA: Pengecekan dilakukan paling awal sebelum UPDATE/ALTER TABLE apa pun
        $activeCount = DB::table('bookings')->where('status', 'Aktif')->count();

        if ($activeCount > 0) {
            throw new RuntimeException(
                "Rollback dibatalkan secara aman: Masih terdapat {$activeCount} data booking berstatus 'Aktif'. ".
                'Harap selesaikan atau ubah status data tersebut secara manual sebelum mengembalikan ENUM status ke versi awal.'
            );
        }

        // Jalankan pembalikan data HANYA jika tidak ada lagi booking berstatus 'Aktif'
        DB::statement("UPDATE bookings SET status = 'Disetujui' WHERE status = 'Menunggu Pembayaran' AND midtrans_order_id IS NULL AND id NOT IN (
            SELECT booking_id FROM pembayarans WHERE booking_id IS NOT NULL
        )");

        // Kembalikan definisi ENUM status ke versi legacy
        DB::statement("ALTER TABLE bookings MODIFY status ENUM(
            'Menunggu Validasi',
            'Disetujui',
            'Menunggu Pembayaran',
            'Ditolak',
            'Kadaluarsa',
            'Dibatalkan',
            'Selesai'
        ) NOT NULL DEFAULT 'Menunggu Validasi'");
    }
};
