<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Tangani kolom midtrans_transaction_id
        if (! Schema::hasColumn('pembayarans', 'midtrans_transaction_id')) {
            Schema::table('pembayarans', function (Blueprint $table) {
                $table->string('midtrans_transaction_id', 191)->nullable()->after('metode_pembayaran');
            });
        } else {
            DB::statement('ALTER TABLE pembayarans MODIFY midtrans_transaction_id VARCHAR(191) NULL');
        }

        // 2. Tangani kolom-kolom lainnya secara parsial-safe
        if (! Schema::hasColumn('pembayarans', 'midtrans_payment_type')) {
            Schema::table('pembayarans', function (Blueprint $table) {
                $table->string('midtrans_payment_type', 50)->nullable()->after('midtrans_transaction_id');
            });
        }

        if (! Schema::hasColumn('pembayarans', 'catatan_refund')) {
            Schema::table('pembayarans', function (Blueprint $table) {
                $table->text('catatan_refund')->nullable()->after('catatan_pemilik');
            });
        }

        if (! Schema::hasColumn('pembayarans', 'tanggal_refund')) {
            Schema::table('pembayarans', function (Blueprint $table) {
                $table->date('tanggal_refund')->nullable()->after('catatan_refund');
            });
        }

        // 3. Tambahkan unique index hanya jika belum ada
        if (! Schema::hasIndex('pembayarans', 'pembayarans_midtrans_transaction_id_unique')) {
            Schema::table('pembayarans', function (Blueprint $table) {
                $table->unique('midtrans_transaction_id', 'pembayarans_midtrans_transaction_id_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::table('pembayarans', function (Blueprint $table) {
            if (Schema::hasIndex('pembayarans', 'pembayarans_midtrans_transaction_id_unique')) {
                $table->dropUnique('pembayarans_midtrans_transaction_id_unique');
            }

            $columnsToDrop = [];
            foreach (['midtrans_transaction_id', 'midtrans_payment_type', 'catatan_refund', 'tanggal_refund'] as $col) {
                if (Schema::hasColumn('pembayarans', $col)) {
                    $columnsToDrop[] = $col;
                }
            }

            if (! empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
