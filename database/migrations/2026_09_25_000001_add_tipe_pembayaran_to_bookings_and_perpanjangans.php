<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (! Schema::hasColumn('bookings', 'tipe_pembayaran')) {
                $table->enum('tipe_pembayaran', ['DP', 'Lunas'])
                    ->default('DP')
                    ->after('durasi_bulan');
            }
        });

        Schema::table('perpanjangans', function (Blueprint $table) {
            if (! Schema::hasColumn('perpanjangans', 'tipe_pembayaran')) {
                $table->enum('tipe_pembayaran', ['DP', 'Lunas'])
                    ->default('DP')
                    ->after('durasi_bulan');
            }
        });

        // Pastikan seluruh data yang sudah ada memiliki nilai default 'DP'
        DB::table('bookings')->whereNull('tipe_pembayaran')->update(['tipe_pembayaran' => 'DP']);
        DB::table('perpanjangans')->whereNull('tipe_pembayaran')->update(['tipe_pembayaran' => 'DP']);
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'tipe_pembayaran')) {
                $table->dropColumn('tipe_pembayaran');
            }
        });

        Schema::table('perpanjangans', function (Blueprint $table) {
            if (Schema::hasColumn('perpanjangans', 'tipe_pembayaran')) {
                $table->dropColumn('tipe_pembayaran');
            }
        });
    }
};
