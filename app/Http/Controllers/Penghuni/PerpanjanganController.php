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
    public function index(Request $request)
    {
        $user = Auth::user();
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');

        $query = Perpanjangan::with(['sewa.kamar', 'dpPembayaran'])
            ->where('user_id', $user->id);

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $perpanjangans = $query->latest()->paginate(10)->withQueryString();

        $activeSewa = Sewa::with('kamar')
            ->where('user_id', $user->id)
            ->where('status', 'Aktif')
            ->latest()
            ->first();

        return view('penghuni.perpanjangan.index', compact('perpanjangans', 'activeSewa', 'fromDate', 'toDate'));
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

        $durasiBulan = (int) $request->durasi_bulan;

        if (!$activeSewa->tanggal_selesai) {
            return redirect()->route('penghuni.perpanjangan.index')
                ->with('error', 'Data masa sewa aktif tidak lengkap. Silakan hubungi pemilik kost.');
        }

        $tanggalMulaiBaru = Carbon::parse($activeSewa->tanggal_selesai)->addDay();
        $tanggalSelesaiBaru = $tanggalMulaiBaru->copy()->addMonths($durasiBulan)->subDay();
        $nominalTotal = (float) $activeSewa->harga_per_bulan * $durasiBulan;
        $dpPersen = 30; // Default persentase DP 30% sesuai Model B
        $nominalDp = ($nominalTotal * $dpPersen) / 100;

        $perpanjangan = Perpanjangan::create([
            'sewa_id' => $activeSewa->id,
            'user_id' => Auth::id(),
            'durasi_bulan' => $durasiBulan,
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
