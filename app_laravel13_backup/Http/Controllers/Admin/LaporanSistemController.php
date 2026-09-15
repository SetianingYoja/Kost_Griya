<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Keluhan;
use App\Models\Pembayaran;
use App\Models\RiwayatAktivitas;
use App\Models\User;
use Illuminate\Http\Request;

class LaporanSistemController extends Controller
{
    public function index()
    {
        $totalPendapatanAllTime = Pembayaran::where('status', 'Lunas')->sum('nominal');
        $totalBookingAllTime = Booking::count();
        $totalUsers = User::count();
        $totalKamars = Kamar::count();
        $totalKeluhans = Keluhan::count();

        $auditLogs = RiwayatAktivitas::with('user')->latest()->paginate(20);

        return view('admin.laporan.index', compact(
            'totalPendapatanAllTime',
            'totalBookingAllTime',
            'totalUsers',
            'totalKamars',
            'totalKeluhans',
            'auditLogs'
        ));
    }
}
