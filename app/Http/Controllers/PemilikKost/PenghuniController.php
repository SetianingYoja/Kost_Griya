<?php

namespace App\Http\Controllers\PemilikKost;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class PenghuniController extends Controller
{
    /**
     * Tampilkan daftar data penghuni (Read-Only).
     */
    public function index(Request $request)
    {
        $status = $request->query('status', 'aktif');

        if ($status === 'aktif') {
            $penghunis = User::whereHas('sewas', function ($q) {
                $q->where('status', 'Aktif');
            })->with(['activeSewa.kamar.tipeKamar'])
                ->latest()
                ->paginate(10);
        } else {
            $penghunis = User::whereHas('role', function ($q) {
                $q->where('slug', 'penghuni');
            })->with(['activeSewa.kamar', 'sewas'])
                ->latest()
                ->paginate(10);
        }

        return view('pemilik-kost.penghuni.index', compact('penghunis', 'status'));
    }

    /**
     * Tampilkan detail penghuni (Read-Only).
     */
    public function show($id)
    {
        $penghuni = User::with([
            'role',
            'sewas.kamar.tipeKamar',
            'bookings.kamar',
            'pembayarans',
            'tagihans.kamar',
            'keluhans.kamar',
            'ratings',
            'riwayatAktivitas',
        ])->findOrFail($id);

        return view('pemilik-kost.penghuni.show', compact('penghuni'));
    }
}
