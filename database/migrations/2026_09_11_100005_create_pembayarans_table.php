<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pembayarans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_pembayaran')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->foreignId('tagihan_id')->nullable()->constrained('tagihans')->nullOnDelete();
            $table->foreignId('perpanjangan_id')->nullable()->constrained('perpanjangans')->nullOnDelete();
            $table->enum('jenis_pembayaran', [
                'Booking Awal',
                'Tagihan Bulanan',
                'DP Perpanjangan',
                'Pelunasan Perpanjangan'
            ]);
            $table->decimal('nominal', 12, 2);
            $table->string('metode_pembayaran')->default('Transfer Bank');
            $table->string('nama_pengirim')->nullable();
            $table->string('bank_pengirim')->nullable();
            $table->string('bukti_pembayaran')->nullable();
            $table->date('tanggal_bayar')->nullable();
            $table->enum('status', [
                'Menunggu Validasi',
                'Lunas',
                'Ditolak'
            ])->default('Menunggu Validasi');
            $table->text('catatan_penghuni')->nullable();
            $table->text('catatan_pemilik')->nullable();
            $table->timestamp('diverifikasi_pada')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pembayarans');
    }
};
