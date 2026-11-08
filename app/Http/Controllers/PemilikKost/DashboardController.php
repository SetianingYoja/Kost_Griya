<?php

namespace App\Http\Controllers\PemilikKost;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Keluhan;
use App\Models\Pembayaran;
use App\Models\Rating;
use App\Models\Sewa;
use App\Models\Tagihan;

class DashboardController extends Controller
{
    /**
     * Dashboard Monitoring Pemilik Kost (Read-Only).
     * Menyajikan data pemantauan statistik okupansi, keuangan, dan status tanpa aksi perubahan/mutasi data.
     */
    public function index()
    {
        Booking::ensureActiveBookingIntegrity();

        // Statistik Okupansi & Kamar
        $totalKamar = Kamar::count();
        $kamarTersedia = Kamar::where('status', 'Tersedia')->count();
        $kamarTerisi = Kamar::where('status', 'Terisi')->count();
        $totalPenghuniAktif = Sewa::where('status', 'Aktif')->distinct('user_id')->count('user_id');

        // Statistik Keuangan & Transaksi
        $pendapatanBulanIni = Pembayaran::where('status', 'Lunas')
            ->whereMonth('tanggal_bayar', now()->month)
            ->whereYear('tanggal_bayar', now()->year)
            ->sum('nominal');

        $totalPendapatanTahunIni = Pembayaran::where('status', 'Lunas')
            ->whereYear('tanggal_bayar', now()->year)
            ->sum('nominal');

        $tagihanBelumLunas = Tagihan::whereIn('status', ['Belum Dibayar', 'Terlambat'])->count();
        $bookingMenunggu = Booking::where('status', 'Menunggu Validasi')->count();
        $keluhanMenunggu = Keluhan::where('status', 'Menunggu')->count();

        // Feed Ringkasan Aktivitas Terbaru (Read-Only)
        $recentBookings = Booking::with(['user', 'kamar'])->latest()->take(5)->get();
        $recentPembayarans = Pembayaran::with(['user', 'booking.kamar', 'tagihan'])->latest()->take(5)->get();
        $recentKeluhans = Keluhan::with(['user', 'kamar'])->latest()->take(5)->get();
        $recentRatings = Rating::with(['user'])->latest()->take(5)->get();

        return view('pemilik-kost.dashboard', compact(
            'totalKamar',
            'kamarTersedia',
            'kamarTerisi',
            'totalPenghuniAktif',
            'pendapatanBulanIni',
            'totalPendapatanTahunIni',
            'tagihanBelumLunas',
            'bookingMenunggu',
            'keluhanMenunggu',
            'recentBookings',
            'recentPembayarans',
            'recentKeluhans',
            'recentRatings'
        ));
    }
}
