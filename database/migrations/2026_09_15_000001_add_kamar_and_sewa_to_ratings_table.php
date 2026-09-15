<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ratings', function (Blueprint $table) {
            $table->foreignId('kamar_id')->nullable()->after('user_id')->constrained('kamar')->nullOnDelete();
            $table->foreignId('sewa_id')->nullable()->after('kamar_id')->constrained('sewas')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ratings', function (Blueprint $table) {
            $table->dropForeign(['kamar_id']);
            $table->dropForeign(['sewa_id']);
            $table->dropColumn(['kamar_id', 'sewa_id']);
        });
    }
};
