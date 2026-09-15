<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('kode_booking')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('kamar_id')->constrained('kamar')->onDelete('restrict');
            $table->date('tanggal_mulai');
            $table->integer('durasi_bulan')->default(1);
            $table->decimal('total_harga', 12, 2);
            $table->text('catatan')->nullable();
            $table->enum('status', [
                'Menunggu Validasi',
                'Disetujui',
                'Menunggu Pembayaran',
                'Ditolak',
                'Kadaluarsa',
                'Dibatalkan',
                'Selesai'
            ])->default('Menunggu Validasi');
            $table->text('alasan_penolakan')->nullable();
            $table->timestamp('batas_pembayaran')->nullable();
            $table->timestamps();
        });

        Schema::create('sewas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('kamar_id')->constrained('kamar')->onDelete('restrict');
            $table->foreignId('booking_id')->nullable()->constrained('bookings')->nullOnDelete();
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->enum('status', ['Aktif', 'Selesai', 'Dibatalkan'])->default('Aktif');
            $table->decimal('harga_per_bulan', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sewas');
        Schema::dropIfExists('bookings');
    }
};
