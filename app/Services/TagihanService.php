<?php

namespace App\Services;

use App\Models\Sewa;
use App\Models\Tagihan;
use Carbon\Carbon;
use Illuminate\Support\Str;

class TagihanService
{
    /**
     * Generate monthly invoices (Tagihan) for a given lease (Sewa).
     *
     * @param  Sewa  $sewa
     * @param  int|null  $startMonth  Optional start month (1‑based). If null, start from month 1.
     * @return void
     */
    public function generateMonthlyTagihan(Sewa $sewa, ?int $startMonth = null): void
    {
        // Ensure dates are Carbon instances
        $startDate = Carbon::parse($sewa->tanggal_mulai);
        $endDate   = Carbon::parse($sewa->tanggal_selesai);

        // Total months inclusive (diffInMonths is exclusive, so add 1)
        $totalMonths = $startDate->diffInMonths($endDate) + 1;

        $fromMonth = $startMonth ?? 1;
        if ($fromMonth < 1) {
            $fromMonth = 1;
        }

        for ($i = $fromMonth; $i <= $totalMonths; $i++) {
            $periodDate = $startDate->copy()->addMonths($i - 1);
            $periodeLabel = $periodDate->format('F Y');

            $attributes = [
                'sewa_id'  => $sewa->id,
                'bulan_ke' => $i,
            ];

            $values = [
                'nomor_tagihan'       => 'TAG-' . date('Ymd') . '-' . strtoupper(Str::random(5)),
                'user_id'             => $sewa->user_id,
                'kamar_id'            => $sewa->kamar_id,
                'periode'             => $periodeLabel,
                'nominal'             => $sewa->harga_per_bulan,
                'total_bayar'         => $sewa->harga_per_bulan,
                'tanggal_jatuh_tempo'=> $periodDate->toDateString(),
                'status'              => 'Belum Dibayar',
            ];

            Tagihan::firstOrCreate($attributes, $values);
        }
    }
}
