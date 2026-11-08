<?php

namespace App\Http\Controllers\PemilikKost;

use App\Http\Controllers\Controller;
use App\Models\Kamar;
use App\Models\Keluhan;
use App\Models\Pembayaran;
use App\Models\Rating;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    /**
     * Tampilkan laporan operasional dan rekap keuangan (Read-Only).
     */
    public function index(Request $request)
    {
        $now = Carbon::now();
        $defaultTanggalDari = $now->copy()->startOfMonth()->toDateString();
        $defaultTanggalSampai = $now->copy()->endOfMonth()->toDateString();

        $inputDari = $request->query('tanggal_dari');
        $inputSampai = $request->query('tanggal_sampai');

        if (! $inputDari && ! $inputSampai) {
            $tanggalDari = $defaultTanggalDari;
            $tanggalSampai = $defaultTanggalSampai;
        } else {
            $tanggalDari = $inputDari ?: $defaultTanggalDari;
            $tanggalSampai = $inputSampai ?: $defaultTanggalSampai;
        }

        try {
            $startDate = Carbon::parse($tanggalDari)->startOfDay();
        } catch (\Throwable) {
            $startDate = Carbon::parse($defaultTanggalDari)->startOfDay();
            $tanggalDari = $defaultTanggalDari;
        }

        try {
            $endDate = Carbon::parse($tanggalSampai)->endOfDay();
        } catch (\Throwable) {
            $endDate = Carbon::parse($defaultTanggalSampai)->endOfDay();
            $tanggalSampai = $defaultTanggalSampai;
        }

        if ($startDate->gt($endDate)) {
            $temp = $startDate;
            $startDate = $endDate->copy()->startOfDay();
            $endDate = $temp->copy()->endOfDay();
            $tanggalDari = $startDate->toDateString();
            $tanggalSampai = $endDate->toDateString();
        }

        // Keuangan / Pendapatan Periode Ini
        $pembayarans = Pembayaran::with([
            'user',
            'booking.kamar',
            'booking.sewa',
            'perpanjangan.sewa.kamar',
            'tagihan.sewa.kamar',
        ])
            ->where('status', 'Lunas')
            ->whereBetween('tanggal_bayar', [$tanggalDari, $tanggalSampai])
            ->latest('tanggal_bayar')
            ->get();

        $pembayarans->each(function (Pembayaran $pembayaran): void {
            $pembayaran->setAttribute('periode_sewa', match ($pembayaran->jenis_pembayaran) {
                'Booking Awal' => $this->formatRentPeriod(
                    $pembayaran->booking?->sewa?->tanggal_mulai,
                    $pembayaran->booking?->sewa?->tanggal_selesai
                ),
                'DP Perpanjangan', 'Pelunasan Perpanjangan', 'Lunas Perpanjangan' => $this->formatRentPeriod(
                    $pembayaran->perpanjangan?->tanggal_mulai_baru,
                    $pembayaran->perpanjangan?->tanggal_selesai_baru
                ),
                'Tagihan Bulanan' => $pembayaran->tagihan?->periode ?: '-',
                default => '-',
            });
        });

        $totalPendapatan = $pembayarans->sum('nominal');

        // Okupansi Kamar Periode Ini
        $totalKamar = Kamar::count();
        $today = Carbon::today()->toDateString();
        $isCurrentDateCovered = ($tanggalDari <= $today && $tanggalSampai >= $today);

        $kamarTerisi = Kamar::where(function ($query) use ($tanggalDari, $tanggalSampai, $isCurrentDateCovered): void {
            $query->whereHas('sewas', function ($q) use ($tanggalDari, $tanggalSampai): void {
                $q->whereIn('status', ['Aktif', 'Selesai'])
                    ->where('tanggal_mulai', '<=', $tanggalSampai)
                    ->where('tanggal_selesai', '>=', $tanggalDari);
            });

            if ($isCurrentDateCovered) {
                $query->orWhere('status', 'Terisi');
            }
        })->count();

        $kamarTidakTersedia = Kamar::where('status', 'Tidak tersedia')->count();
        $kamarTersedia = max(0, $totalKamar - $kamarTerisi - $kamarTidakTersedia);
        $okupansiPersen = $totalKamar > 0 ? round(($kamarTerisi / $totalKamar) * 100, 1) : 0;

        // Keluhan & Kepuasan Periode Ini
        $totalKeluhanBulanIni = Keluhan::whereBetween('created_at', [$startDate, $endDate])->count();
        $keluhanSelesaiBulanIni = Keluhan::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'Selesai')
            ->count();

        // Rating Kost Periode Ini
        $avgRatingKost = (float) (Rating::where('jenis_rating', 'Kost')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->avg('skor') ?? 5.0);

        $avgRatingKeluhan = (float) (Rating::where('jenis_rating', 'Penanganan Keluhan')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->avg('skor') ?? 5.0);

        $bulan = $startDate->format('m');
        $tahun = $startDate->format('Y');

        return view('pemilik-kost.laporan.index', compact(
            'tanggalDari',
            'tanggalSampai',
            'bulan',
            'tahun',
            'pembayarans',
            'totalPendapatan',
            'totalKamar',
            'kamarTerisi',
            'kamarTersedia',
            'kamarTidakTersedia',
            'okupansiPersen',
            'totalKeluhanBulanIni',
            'keluhanSelesaiBulanIni',
            'avgRatingKost',
            'avgRatingKeluhan'
        ));
    }

    private function formatRentPeriod(?CarbonInterface $start, ?CarbonInterface $end): string
    {
        if (! $start || ! $end) {
            return '-';
        }

        return $start->format('d M Y').' - '.$end->format('d M Y');
    }
}
