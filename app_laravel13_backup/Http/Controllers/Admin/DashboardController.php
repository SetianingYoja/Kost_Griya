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
        // Statistik Super Admin (PRD Section 17)
        $totalUser = User::count();
        $totalPemilik = User::whereHas('role', fn($q) => $q->where('slug', 'pemilik-kost'))->count();
        $totalPenghuni = User::whereHas('role', fn($q) => $q->where('slug', 'penghuni'))->count();
        $totalKamar = Kamar::count();
        $totalBooking = Booking::count();
        $totalTransaksi = Pembayaran::count();
        $totalKeluhan = Keluhan::count();

        $recentUsers = User::with('role')->latest()->take(5)->get();
        $recentAktivitas = RiwayatAktivitas::with('user')->latest()->take(10)->get();

        return view('admin.dashboard', compact(
            'totalUser',
            'totalPemilik',
            'totalPenghuni',
            'totalKamar',
            'totalBooking',
            'totalTransaksi',
            'totalKeluhan',
            'recentUsers',
            'recentAktivitas'
        ));
    }
}
