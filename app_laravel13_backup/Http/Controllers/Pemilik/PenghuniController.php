<?php

namespace App\Http\Controllers\Pemilik;

use App\Http\Controllers\Controller;
use App\Models\Sewa;
use App\Models\User;
use Illuminate\Http\Request;

class PenghuniController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status', 'aktif');

        if ($status === 'aktif') {
            // Penghuni dengan sewa aktif
            $penghunis = User::whereHas('sewas', function ($q) {
                $q->where('status', 'Aktif');
            })->with(['activeSewa.kamar.tipeKamar'])
              ->latest()
              ->paginate(10);
        } else {
            // Seluruh pengguna dengan role penghuni (termasuk alumni / calon)
            $penghunis = User::whereHas('role', function ($q) {
                $q->where('slug', 'penghuni');
            })->with(['activeSewa.kamar', 'sewas'])
              ->latest()
              ->paginate(10);
        }

        return view('pemilik.penghuni.index', compact('penghunis', 'status'));
    }

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
            'riwayatAktivitas'
        ])->findOrFail($id);

        return view('pemilik.penghuni.show', compact('penghuni'));
    }
}
