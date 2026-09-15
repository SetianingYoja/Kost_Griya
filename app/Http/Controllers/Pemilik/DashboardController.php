<?php

namespace App\Http\Controllers\Pemilik;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Keluhan;
use App\Models\Pembayaran;
use App\Models\Rating;
use App\Models\Sewa;
use App\Models\Tagihan;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistik Operasional (PRD Section 16)
        $totalKamar = Kamar::count();
        $kamarTersedia = Kamar::where('status', 'Tersedia')->count();
        $kamarTerisi = Kamar::where('status', 'Terisi')->count();
        $totalPenghuniAktif = Sewa::where('status', 'Aktif')->distinct('user_id')->count('user_id');
        
        $bookingMenunggu = Booking::where('status', 'Menunggu Validasi')->count();
        $pembayaranMenunggu = Pembayaran::where('status', 'Menunggu Validasi')->count();
        $tagihanBelumLunas = Tagihan::whereIn('status', ['Belum Dibayar', 'Terlambat'])->count();
        $keluhanMenunggu = Keluhan::where('status', 'Menunggu')->count();

        // Feed Aktivitas & Antrean Terbaru
        $recentBookings = Booking::with(['user', 'kamar'])->latest()->take(5)->get();
        $recentPembayarans = Pembayaran::with(['user', 'booking.kamar', 'tagihan'])->latest()->take(5)->get();
        $recentKeluhans = Keluhan::with(['user', 'kamar'])->latest()->take(5)->get();
        $recentRatings = Rating::with(['user', 'keluhan'])->latest()->take(5)->get();

        $pendapatanBulanIni = Pembayaran::where('status', 'Lunas')
            ->whereMonth('tanggal_bayar', now()->month)
            ->whereYear('tanggal_bayar', now()->year)
            ->sum('nominal');

        return view('pemilik.dashboard', compact(
            'totalKamar',
            'kamarTersedia',
            'kamarTerisi',
            'totalPenghuniAktif',
            'bookingMenunggu',
            'pembayaranMenunggu',
            'tagihanBelumLunas',
            'keluhanMenunggu',
            'recentBookings',
            'recentPembayarans',
            'recentKeluhans',
            'recentRatings',
            'pendapatanBulanIni'
        ));
    }
}
