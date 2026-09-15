<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perpanjangans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sewa_id')->constrained('sewas')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->integer('durasi_bulan')->default(1);
            $table->date('tanggal_mulai_baru');
            $table->date('tanggal_selesai_baru');
            $table->decimal('nominal_total', 12, 2);
            $table->decimal('nominal_dp', 12, 2)->default(0);
            $table->integer('dp_persen')->default(30);
            $table->enum('status', [
                'Menunggu Validasi',
                'Disetujui',
                'Menunggu Pembayaran DP',
                'DP Dibayar',
                'Aktif',
                'Ditolak'
            ])->default('Menunggu Validasi');
            $table->text('catatan')->nullable();
            $table->text('alasan_penolakan')->nullable();
            $table->timestamps();
        });

        Schema::create('tagihans', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_tagihan')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('kamar_id')->constrained('kamar')->onDelete('restrict');
            $table->foreignId('sewa_id')->nullable()->constrained('sewas')->nullOnDelete();
            $table->foreignId('perpanjangan_id')->nullable()->constrained('perpanjangans')->nullOnDelete();
            $table->string('periode'); // e.g. "Oktober 2026", "Sewa Awal", dll.
            $table->integer('bulan_ke')->default(1);
            $table->decimal('nominal', 12, 2);
            $table->decimal('potongan_dp', 12, 2)->default(0); // Memastikan Model B tidak double charge
            $table->decimal('total_bayar', 12, 2);
            $table->date('tanggal_jatuh_tempo');
            $table->enum('status', [
                'Belum Dibayar',
                'Menunggu Validasi',
                'Lunas',
                'Terlambat'
            ])->default('Belum Dibayar');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tagihans');
        Schema::dropIfExists('perpanjangans');
    }
};
