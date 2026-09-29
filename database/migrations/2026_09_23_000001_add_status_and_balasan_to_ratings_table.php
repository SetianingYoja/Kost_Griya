<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ratings', function (Blueprint $table) {
            $table->string('status', 30)->default('Menunggu Validasi')->after('komentar');
            $table->text('balasan')->nullable()->after('status');
            $table->timestamp('dibalas_pada')->nullable()->after('balasan');
            $table->timestamp('disetujui_pada')->nullable()->after('dibalas_pada');
        });

        // Set ulasan lama yang sudah ada menjadi Disetujui
        DB::table('ratings')->where('status', 'Menunggu Validasi')->update(['status' => 'Disetujui']);
    }

    public function down(): void
    {
        Schema::table('ratings', function (Blueprint $table) {
            $table->dropColumn(['status', 'balasan', 'dibalas_pada', 'disetujui_pada']);
        });
    }
};
