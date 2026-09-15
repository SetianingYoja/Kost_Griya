<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keluhans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_keluhan')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('kamar_id')->nullable()->constrained('kamar')->nullOnDelete();
            $table->string('judul');
            $table->text('isi');
            $table->string('foto')->nullable();
            $table->enum('status', [
                'Menunggu',
                'Diproses',
                'Selesai',
                'Ditolak'
            ])->default('Menunggu');
            $table->text('tanggapan')->nullable();
            $table->timestamp('selesai_pada')->nullable();
            $table->timestamps();
        });

        Schema::create('ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('keluhan_id')->nullable()->constrained('keluhans')->nullOnDelete();
            $table->enum('jenis_rating', [
                'Kost',
                'Penanganan Keluhan'
            ])->default('Kost');
            $table->unsignedTinyInteger('skor'); // 1 sampai 5
            $table->text('komentar')->nullable();
            $table->timestamps();
        });

        Schema::create('riwayat_aktivitas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->string('tipe', 50)->default('info'); // info, success, warning, danger
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_aktivitas');
        Schema::dropIfExists('ratings');
        Schema::dropIfExists('keluhans');
    }
};
