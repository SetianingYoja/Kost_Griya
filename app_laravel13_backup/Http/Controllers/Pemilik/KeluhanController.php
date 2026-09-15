<?php

namespace App\Http\Controllers\Pemilik;

use App\Http\Controllers\Controller;
use App\Models\Keluhan;
use App\Models\RiwayatAktivitas;
use Carbon\Carbon;
use Illuminate\Http\Request;

class KeluhanController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $query = Keluhan::with(['user', 'kamar', 'rating']);

        if ($status) {
            $query->where('status', $status);
        }

        $keluhans = $query->latest()->paginate(10)->withQueryString();

        return view('pemilik.keluhan.index', compact('keluhans', 'status'));
    }

    public function show($id)
    {
        $keluhan = Keluhan::with(['user', 'kamar', 'rating'])->findOrFail($id);
        return view('pemilik.keluhan.show', compact('keluhan'));
    }

    public function update(Request $request, $id)
    {
        $keluhan = Keluhan::findOrFail($id);

        $request->validate([
            'status' => ['required', 'in:Menunggu,Diproses,Selesai,Ditolak'],
            'tanggapan' => ['required', 'string', 'max:2000'],
        ], [
            'tanggapan.required' => 'Tanggapan atau keterangan penanganan keluhan wajib diisi.',
        ]);

        $keluhan->status = $request->status;
        $keluhan->tanggapan = $request->tanggapan;

        if ($request->status === 'Selesai') {
            $keluhan->selesai_pada = Carbon::now();
        }

        $keluhan->save();

        RiwayatAktivitas::catat(
            $keluhan->user_id,
            'Keluhan ' . $request->status,
            'Penanganan keluhan "' . $keluhan->judul . '" berstatus ' . $request->status . '. Tanggapan: ' . $request->tanggapan,
            $request->status === 'Selesai' ? 'success' : 'info'
        );

        return back()->with('success', 'Status keluhan dan tanggapan berhasil diperbarui.');
    }
}
