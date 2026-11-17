<?php

namespace App\Http\Controllers\Pemilik;

use App\Http\Controllers\Controller;
use App\Models\Kamar;
use App\Models\Keluhan;
use App\Models\Pembayaran;
use App\Models\Rating;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->query('bulan', date('m'));
        $tahun = $request->query('tahun', date('Y'));

        // Keuangan / Pendapatan
        $pembayarans = Pembayaran::with([
            'user',
            'booking.kamar',
            'booking.sewa',
            'perpanjangan.sewa.kamar',
            'tagihan.sewa.kamar',
        ])
            ->where('status', 'Lunas')
            ->whereMonth('tanggal_bayar', $bulan)
            ->whereYear('tanggal_bayar', $tahun)
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

        // Okupansi
        $totalKamar = Kamar::count();
        $kamarTerisi = Kamar::where('status', 'Terisi')->count();
        $kamarTersedia = Kamar::where('status', 'Tersedia')->count();
        $kamarTidakTersedia = Kamar::where('status', 'Tidak tersedia')->count();
        $okupansiPersen = $totalKamar > 0 ? round(($kamarTerisi / $totalKamar) * 100, 1) : 0;

        // Keluhan & Kepuasan
        $totalKeluhanBulanIni = Keluhan::whereMonth('created_at', $bulan)->whereYear('created_at', $tahun)->count();
        $keluhanSelesaiBulanIni = Keluhan::whereMonth('created_at', $bulan)->whereYear('created_at', $tahun)->where('status', 'Selesai')->count();
        $avgRatingKost = Rating::where('jenis_rating', 'Kost')->avg('skor') ?? 5.0;
        $avgRatingKeluhan = Rating::where('jenis_rating', 'Penanganan Keluhan')->avg('skor') ?? 5.0;

        return view('pemilik.laporan.index', compact(
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
