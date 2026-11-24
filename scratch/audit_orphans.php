<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Support\Facades\DB;
$orphanBookings = DB::table('bookings')->whereNull('user_id')->orWhereNotIn('user_id', function($q){$q->select('id')->from('users');})->pluck('id');
$orphanPembayarans = DB::table('pembayarans')->whereNull('user_id')->orWhereNotIn('user_id', function($q){$q->select('id')->from('users');})->pluck('id');
echo "Orphan Bookings count: " . $orphanBookings->count() . "\n";
echo "Orphan Booking IDs: " . $orphanBookings->implode(',') . "\n";
echo "Orphan Pembayarans count: " . $orphanPembayarans->count() . "\n";
echo "Orphan Pembayaran IDs: " . $orphanPembayarans->implode(',') . "\n";
?>