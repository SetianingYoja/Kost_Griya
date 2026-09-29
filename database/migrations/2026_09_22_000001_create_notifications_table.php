<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->string('deduplication_key', 191);
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->unique(
                ['notifiable_type', 'notifiable_id', 'deduplication_key'],
                'notifications_notifiable_deduplication_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
