<?php

namespace App\Console\Commands;

use App\Models\Booking;
use App\Models\Kamar;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ExpireBookings extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'bookings:expire';

    /**
     * The console command description.
     */
    protected $description = 'Expire unpaid bookings past the payment deadline and release rooms.';

    public function handle(): int
    {
        $processed = 0;

        Booking::query()
            ->where('status', 'Menunggu Pembayaran')
            ->where('batas_pembayaran', '<=', now())
            ->with(['kamar'])
            ->chunkById(100, function ($bookings) use (&$processed) {
                foreach ($bookings as $booking) {
                    DB::transaction(function () use ($booking, &$processed) {
                        $b = Booking::whereKey($booking->id)->lockForUpdate()->first();
                        if (! $b) {
                            return;
                        }

                        $hasPayment = $b->pembayarans()
                            ->whereIn('status', ['Lunas', 'Menunggu Validasi'])
                            ->exists();
                        if ($hasPayment) {
                            return;
                        }

                        $b->status = 'Kadaluarsa';
                        $b->save();

                        if ($b->kamar) {
                            $b->kamar->releaseIfAvailable($b->id);
                        }

                        $processed++;
                    });
                }
            });

        $this->info("Expired {$processed} booking(s).");
        return self::SUCCESS;
    }
}
