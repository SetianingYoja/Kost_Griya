<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipe_kamar', function (Blueprint $table) {
            $table->id();
            $table->string('nama_tipe')->unique(); // contoh: Standar, Deluxe, VIP
            $table->string('slug')->unique();
            $table->decimal('harga_bulanan', 12, 2);
            $table->text('fasilitas_dasar')->nullable();
            $table->text('deskripsi')->nullable();
            $table->timestamps();
        });

        Schema::create('kamar', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tipe_kamar_id')->constrained('tipe_kamar')->onDelete('restrict');
            $table->string('nomor_kamar')->unique();
            $table->integer('lantai')->default(1);
            $table->decimal('harga', 12, 2);
            $table->text('fasilitas')->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('foto')->nullable();
            $table->enum('status', ['Tersedia', 'Terisi', 'Tidak tersedia'])->default('Tersedia');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kamar');
        Schema::dropIfExists('tipe_kamar');
    }
};
