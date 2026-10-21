<?php

namespace App\Console\Commands;

use App\Models\Perpanjangan;
use App\Models\Sewa;
use App\Services\TagihanService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class GeneratePerpanjanganTagihan extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'perpanjangan:generate-tagihan';

    /**
     * The console command description.
     */
    protected $description = 'Generate monthly invoices for approved lease extensions and extend lease end date.';

    public function handle(TagihanService $tagihanService): int
    {
        $processed = 0;
        // Process perpanjangan with status indicating it is approved but not yet applied.
        Perpanjangan::query()
            ->where('status', 'Disetujui')
            ->with(['sewa.kamar'])
            ->chunkById(100, function ($perpanjangans) use (&$processed, $tagihanService) {
                foreach ($perpanjangans as $perpanjangan) {
                    DB::transaction(function () use ($perpanjangan, $tagihanService, &$processed) {
                        // Lock perpanjangan and related sewa for update
                        $perpanjangan = $perpanjangan->lockForUpdate()->first();
                        $sewa = $perpanjangan->sewa()->lockForUpdate()->first();
                        if (! $sewa) {
                            return;
                        }

                        // Extend sewa end date only once
                        $originalEnd = $sewa->tanggal_selesai;
                        $sewa->tanggal_selesai = Carbon::parse($originalEnd)->addMonths($perpanjangan->durasi_bulan);
                        $sewa->save();

                        // Generate invoices for the newly added months
                        $existingCount = $sewa->tagihans()->count();
                        $tagihanService->generateMonthlyTagihan($sewa, $existingCount + 1);

                        // Mark perpanjangan as processed to avoid re‑running
                        $perpanjangan->status = 'Aktif';
                        $perpanjangan->save();

                        $processed++;
                    });
                }
            });

        $this->info("Processed {$processed} perpanjangan(s).");
        return self::SUCCESS;
    }
}
