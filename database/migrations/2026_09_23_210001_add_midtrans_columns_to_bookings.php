<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->decimal('nominal_dp', 12, 2)->nullable()->after('total_harga');
            $table->string('midtrans_order_id', 100)->nullable()->unique()->after('nominal_dp');
            $table->text('midtrans_snap_token')->nullable()->after('midtrans_order_id');
            $table->string('midtrans_status', 50)->nullable()->after('midtrans_snap_token');
            $table->timestamp('midtrans_paid_at')->nullable()->after('midtrans_status');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropColumn([
                'nominal_dp',
                'midtrans_order_id',
                'midtrans_snap_token',
                'midtrans_status',
                'midtrans_paid_at',
            ]);
        });
    }
};
