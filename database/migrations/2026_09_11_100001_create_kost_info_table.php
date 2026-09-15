<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kost_info', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kost')->default('Kost Putri Griya Ayu');
            $table->string('tagline')->default('Modern Elegant Boarding House');
            $table->text('deskripsi')->nullable();
            $table->text('alamat')->nullable();
            $table->string('no_telepon', 25)->nullable();
            $table->string('email')->nullable();
            $table->string('bank_nama', 50)->default('BCA');
            $table->string('bank_rekening', 50)->default('1234567890');
            $table->string('bank_atas_nama', 100)->default('Kost Putri Griya Ayu');
            $table->text('aturan_kost')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kost_info');
    }
};
