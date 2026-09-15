<?php

namespace App\Http\Controllers\Penghuni;

use App\Http\Controllers\Controller;
use App\Models\Keluhan;
use App\Models\RiwayatAktivitas;
use App\Models\Sewa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class KeluhanController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $keluhans = Keluhan::with(['kamar', 'rating'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        return view('penghuni.keluhan.index', compact('keluhans'));
    }

    public function create()
    {
        $user = Auth::user();
        $activeSewa = Sewa::with('kamar')->where('user_id', $user->id)->where('status', 'Aktif')->first();

        return view('penghuni.keluhan.create', compact('activeSewa'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'judul' => ['required', 'string', 'max:150'],
            'isi' => ['required', 'string', 'max:2000'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:3072'],
        ], [
            'judul.required' => 'Judul keluhan atau laporan wajib diisi.',
            'isi.required' => 'Uraian keluhan wajib diisi dengan jelas.',
        ]);

        $activeSewa = Sewa::where('user_id', Auth::id())->where('status', 'Aktif')->first();
        $path = null;

        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('keluhan', 'public');
        }

        $kodeKeluhan = 'KLH-' . date('Ymd') . '-' . strtoupper(Str::random(5));

        $keluhan = Keluhan::create([
            'kode_keluhan' => $kodeKeluhan,
            'user_id' => Auth::id(),
            'kamar_id' => $activeSewa?->kamar_id,
            'judul' => $request->judul,
            'isi' => $request->isi,
            'foto' => $path,
            'status' => 'Menunggu',
        ]);

        RiwayatAktivitas::catat(
            Auth::id(),
            'Kirim Keluhan Fasilitas',
            'Mengirimkan laporan: "' . $request->judul . '" (' . $kodeKeluhan . ')',
            'warning'
        );

        return redirect()->route('penghuni.keluhan.index')
            ->with('success', 'Keluhan Anda berhasil dikirimkan. Pemilik kost akan segera menindaklanjuti.');
    }

    public function show($id)
    {
        $keluhan = Keluhan::with(['kamar', 'rating'])->where('user_id', Auth::id())->findOrFail($id);

        return view('penghuni.keluhan.show', compact('keluhan'));
    }
}
