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
    public function index(Request $request)
    {
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');

        $totalPendapatanAllTime = Pembayaran::where('status', 'Lunas')->sum('nominal');
        $totalBookingAllTime = Booking::count();
        $totalUsers = User::count();
        $totalKamars = Kamar::count();
        $totalKeluhans = Keluhan::count();

        $auditLogsQuery = RiwayatAktivitas::with('user')->latest();

        if ($fromDate) {
            $auditLogsQuery->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $auditLogsQuery->whereDate('created_at', '<=', $toDate);
        }

        $auditLogs = $auditLogsQuery->paginate(20)->withQueryString();

        return view('admin.laporan.index', compact(
            'totalPendapatanAllTime',
            'totalBookingAllTime',
            'totalUsers',
            'totalKamars',
            'totalKeluhans',
            'auditLogs',
            'fromDate',
            'toDate'
        ));
    }
}
