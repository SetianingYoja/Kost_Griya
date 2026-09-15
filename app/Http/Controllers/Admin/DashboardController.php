<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Keluhan;
use App\Models\Pembayaran;
use App\Models\RiwayatAktivitas;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Statistik Super Admin (Sistem & Operasional)
        $totalUser = User::count();
        $totalKamar = Kamar::count();
        $totalPenghuni = User::whereHas('role', fn($q) => $q->where('slug', 'penghuni'))->count();
        $totalBooking = Booking::count();

        $kamarTersedia = Kamar::where('status', 'Tersedia')->count();
        $kamarTerisi = Kamar::where('status', 'Terisi')->count();
        $pembayaranMenunggu = Pembayaran::where('status', 'Menunggu')->count();
        
        // Asumsi Tagihan memiliki status 'Belum Lunas'
        $tagihanBelumLunas = \App\Models\Tagihan::where('status', 'Belum Lunas')->count();

        $recentUsers = User::with('role')->latest()->take(5)->get();
        $recentAktivitas = RiwayatAktivitas::with('user')->latest()->take(10)->get();

        return view('admin.dashboard', compact(
            'totalUser',
            'totalKamar',
            'totalPenghuni',
            'totalBooking',
            'kamarTersedia',
            'kamarTerisi',
            'pembayaranMenunggu',
            'tagihanBelumLunas',
            'recentUsers',
            'recentAktivitas'
        ));
    }
}
