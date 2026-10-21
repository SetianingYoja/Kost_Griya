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
        if (DB::getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE kamar MODIFY status ENUM(
                'Tersedia',
                'Sedang Diproses',
                'Terisi',
                'Tidak tersedia'
            ) NOT NULL DEFAULT 'Tersedia'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'sqlite') {
            $inProcessCount = DB::table('kamar')->where('status', 'Sedang Diproses')->count();

            if ($inProcessCount > 0) {
                throw new RuntimeException(
                    "Rollback dibatalkan secara aman: Masih terdapat {$inProcessCount} data kamar berstatus 'Sedang Diproses'."
                );
            }

            DB::statement("ALTER TABLE kamar MODIFY status ENUM(
                'Tersedia',
                'Terisi',
                'Tidak tersedia'
            ) NOT NULL DEFAULT 'Tersedia'");
        }
    }
};
