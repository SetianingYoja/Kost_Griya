<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kamar_fotos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kamar_id')->constrained('kamar')->onDelete('cascade');
            $table->string('foto');
            $table->boolean('is_cover')->default(false);
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kamar_fotos');
    }
};
