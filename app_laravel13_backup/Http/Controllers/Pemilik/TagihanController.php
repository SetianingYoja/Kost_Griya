<?php

namespace App\Http\Controllers\Pemilik;

use App\Http\Controllers\Controller;
use App\Models\Kamar;
use App\Models\RiwayatAktivitas;
use App\Models\Sewa;
use App\Models\Tagihan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TagihanController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $query = Tagihan::with(['user', 'kamar', 'latestPembayaran']);

        if ($status) {
            $query->where('status', $status);
        }

        $tagihans = $query->latest()->paginate(10)->withQueryString();

        return view('pemilik.tagihan.index', compact('tagihans', 'status'));
    }

    public function create()
    {
        // Hanya sewa aktif
        $activeSewas = Sewa::with(['user', 'kamar'])->where('status', 'Aktif')->get();
        return view('pemilik.tagihan.create', compact('activeSewas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'sewa_id' => ['required', 'exists:sewas,id'],
            'periode' => ['required', 'string', 'max:100'],
            'nominal' => ['required', 'numeric', 'min:0'],
            'potongan_dp' => ['nullable', 'numeric', 'min:0'],
            'tanggal_jatuh_tempo' => ['required', 'date'],
        ]);

        $sewa = Sewa::with(['user', 'kamar'])->findOrFail($request->sewa_id);
        $nominal = $request->nominal;
        $potonganDp = $request->potongan_dp ?? 0;
        $totalBayar = max(0, $nominal - $potonganDp);
        $nomorTagihan = 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(4));

        $tagihan = Tagihan::create([
            'nomor_tagihan' => $nomorTagihan,
            'user_id' => $sewa->user_id,
            'kamar_id' => $sewa->kamar_id,
            'sewa_id' => $sewa->id,
            'periode' => $request->periode,
            'bulan_ke' => Tagihan::where('sewa_id', $sewa->id)->count() + 1,
            'nominal' => $nominal,
            'potongan_dp' => $potonganDp,
            'total_bayar' => $totalBayar,
            'tanggal_jatuh_tempo' => $request->tanggal_jatuh_tempo,
            'status' => 'Belum Dibayar',
        ]);

        RiwayatAktivitas::catat(
            $sewa->user_id,
            'Tagihan Sewa Baru Diterbitkan',
            'Tagihan periode ' . $request->periode . ' sebesar Rp ' . number_format($totalBayar, 0, ',', '.') . ' telah diterbitkan.',
            'warning'
        );

        return redirect()->route('pemilik.tagihan.index')
            ->with('success', 'Tagihan ' . $nomorTagihan . ' berhasil dibuat dan dikirim ke penghuni.');
    }

    public function show($id)
    {
        $tagihan = Tagihan::with(['user', 'kamar.tipeKamar', 'pembayarans', 'sewa'])->findOrFail($id);
        return view('pemilik.tagihan.show', compact('tagihan'));
    }
}
