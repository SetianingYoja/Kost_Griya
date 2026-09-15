<?php

namespace App\Http\Controllers\Penghuni;

use App\Http\Controllers\Controller;
use App\Models\Perpanjangan;
use App\Models\RiwayatAktivitas;
use App\Models\Sewa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PerpanjanganController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $perpanjangans = Perpanjangan::with(['sewa.kamar', 'dpPembayaran'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        $activeSewa = Sewa::with('kamar')
            ->where('user_id', $user->id)
            ->where('status', 'Aktif')
            ->latest()
            ->first();

        return view('penghuni.perpanjangan.index', compact('perpanjangans', 'activeSewa'));
    }

    public function create()
    {
        $activeSewa = Sewa::with('kamar.tipeKamar')
            ->where('user_id', Auth::id())
            ->where('status', 'Aktif')
            ->latest()
            ->first();

        if (!$activeSewa) {
            return redirect()->route('penghuni.dashboard')->with('error', 'Anda belum memiliki masa sewa aktif untuk diperpanjang.');
        }

        // Cek jika sedang ada permohonan perpanjangan yang menggantung
        $pending = Perpanjangan::where('sewa_id', $activeSewa->id)
            ->whereIn('status', ['Menunggu Validasi', 'Disetujui', 'Menunggu Pembayaran DP'])
            ->first();

        if ($pending) {
            return redirect()->route('penghuni.perpanjangan.index')
                ->with('info', 'Anda telah memiliki pengajuan perpanjangan yang sedang diproses.');
        }

        return view('penghuni.perpanjangan.create', compact('activeSewa'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'durasi_bulan' => ['required', 'integer', 'min:1', 'max:24'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        $activeSewa = Sewa::with('kamar')
            ->where('user_id', Auth::id())
            ->where('status', 'Aktif')
            ->latest()
            ->firstOrFail();

        $tanggalMulaiBaru = Carbon::parse($activeSewa->tanggal_selesai)->addDay();
        $tanggalSelesaiBaru = $tanggalMulaiBaru->copy()->addMonths($request->durasi_bulan)->subDay();
        $nominalTotal = $activeSewa->harga_per_bulan * $request->durasi_bulan;
        $dpPersen = 30; // Default persentase DP 30% sesuai Model B
        $nominalDp = ($nominalTotal * $dpPersen) / 100;

        $perpanjangan = Perpanjangan::create([
            'sewa_id' => $activeSewa->id,
            'user_id' => Auth::id(),
            'durasi_bulan' => $request->durasi_bulan,
            'tanggal_mulai_baru' => $tanggalMulaiBaru,
            'tanggal_selesai_baru' => $tanggalSelesaiBaru,
            'nominal_total' => $nominalTotal,
            'nominal_dp' => $nominalDp,
            'dp_persen' => $dpPersen,
            'status' => 'Menunggu Validasi',
            'catatan' => $request->catatan,
        ]);

        RiwayatAktivitas::catat(
            Auth::id(),
            'Pengajuan Perpanjangan Sewa',
            'Mengajukan perpanjangan kamar ' . $activeSewa->kamar->nomor_kamar . ' selama ' . $request->durasi_bulan . ' bulan (Model B DP).',
            'info'
        );

        return redirect()->route('penghuni.perpanjangan.index')
            ->with('success', 'Pengajuan perpanjangan sewa berhasil dikirim! Menunggu konfirmasi pemilik kost untuk nominal DP.');
    }

    public function show($id)
    {
        $perpanjangan = Perpanjangan::with(['sewa.kamar', 'dpPembayaran', 'tagihans'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return view('penghuni.perpanjangan.show', compact('perpanjangan'));
    }
}
