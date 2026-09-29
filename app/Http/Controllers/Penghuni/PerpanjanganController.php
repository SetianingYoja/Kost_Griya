<?php

namespace App\Http\Controllers\Penghuni;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Perpanjangan;
use App\Models\RiwayatAktivitas;
use App\Models\Sewa;
use App\Notifications\BusinessNotification;
use App\Services\MidtransService;
use App\Services\Notifications\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PerpanjanganController extends Controller
{
    public function __construct(
        private readonly NotificationService $notificationService,
        private readonly MidtransService $midtransService
    ) {}

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

        $pendingPerpanjangan = $activeSewa
            ? Perpanjangan::where('sewa_id', $activeSewa->id)
                ->whereIn('status', ['Menunggu Validasi', 'Disetujui', 'Menunggu Pembayaran DP'])
                ->latest()
                ->first()
            : null;

        return view('penghuni.perpanjangan.index', compact('perpanjangans', 'activeSewa', 'pendingPerpanjangan', 'fromDate', 'toDate'));
    }

    public function create()
    {
        $activeSewa = Sewa::with('kamar.tipeKamar')
            ->where('user_id', Auth::id())
            ->where('status', 'Aktif')
            ->latest()
            ->first();

        if (! $activeSewa) {
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
            'tipe_pembayaran' => ['required', 'in:DP,Lunas'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ]);

        $activeSewa = Sewa::with('kamar')
            ->where('user_id', Auth::id())
            ->where('status', 'Aktif')
            ->latest()
            ->firstOrFail();

        $durasiBulan = (int) $request->durasi_bulan;

        if (! $activeSewa->tanggal_selesai) {
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
            'tipe_pembayaran' => $request->tipe_pembayaran,
            'nominal_total' => $nominalTotal,
            'nominal_dp' => $request->tipe_pembayaran === 'DP' ? $nominalDp : 0,
            'dp_persen' => $request->tipe_pembayaran === 'DP' ? $dpPersen : 0,
            'status' => 'Menunggu Validasi',
            'catatan' => $request->catatan,
        ]);

        RiwayatAktivitas::catat(
            Auth::id(),
            'Pengajuan Perpanjangan Sewa',
            'Mengajukan perpanjangan kamar '.$activeSewa->kamar->nomor_kamar.' selama '.$request->durasi_bulan.' bulan (Model B DP).',
            'info'
        );

        $notification = new BusinessNotification(
            'perpanjangan',
            'diajukan',
            'Pengajuan perpanjangan baru',
            'Pengajuan perpanjangan untuk kamar '.$activeSewa->kamar->nomor_kamar.' menunggu validasi.',
            'perpanjangan:'.$perpanjangan->id.':diajukan',
            ['entity_type' => 'perpanjangan', 'entity_id' => $perpanjangan->id, 'actor_id' => Auth::id()]
        );

        $this->notificationService->sendToRoleAfterCommit('pemilik-kost', $notification, Auth::id());
        $this->notificationService->sendToRoleAfterCommit('super-admin', $notification, Auth::id());

        $message = $request->tipe_pembayaran === 'Lunas'
            ? 'Pengajuan perpanjangan sewa (Lunas) berhasil dikirim! Menunggu konfirmasi Pemilik Kost.'
            : 'Pengajuan perpanjangan sewa (DP) berhasil dikirim! Menunggu konfirmasi Pemilik Kost untuk nominal DP.';

        return redirect()->route('penghuni.perpanjangan.index')
            ->with('success', $message);
    }

    public function show($id)
    {
        $perpanjangan = Perpanjangan::with(['sewa.kamar', 'dpPembayaran', 'tagihans'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return view('penghuni.perpanjangan.show', compact('perpanjangan'));
    }

    public function showBayar($id)
    {
        $perpanjangan = Perpanjangan::with(['sewa.kamar.tipeKamar'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        if ($perpanjangan->status !== 'Menunggu Pembayaran DP') {
            return redirect()->route('penghuni.perpanjangan.show', $perpanjangan->id)
                ->with('info', 'Pengajuan perpanjangan ini tidak dalam status menunggu pembayaran DP.');
        }

        $user = Auth::user();
        $kamar = $perpanjangan->sewa->kamar;

        // Cegah duplikasi transaksi jika pembayaran perpanjangan sebelumnya masih berstatus pending
        $jenisPembayaran = $perpanjangan->tipe_pembayaran === 'Lunas' ? 'Pelunasan Perpanjangan' : 'DP Perpanjangan';
        $existingPembayaran = Pembayaran::where('perpanjangan_id', $perpanjangan->id)
            ->whereIn('jenis_pembayaran', ['DP Perpanjangan', 'Pelunasan Perpanjangan', 'Lunas Perpanjangan'])
            ->where('status', 'Menunggu Validasi')
            ->whereNotNull('midtrans_transaction_id')
            ->first();

        if ($existingPembayaran) {
            return redirect()->route('penghuni.perpanjangan.show', $perpanjangan->id)
                ->with('info', 'Pembayaran perpanjangan Anda telah tercatat dan sedang menunggu validasi Pemilik Kost.');
        }

        $orderId = 'EXT-'.$perpanjangan->id.'-'.time();

        $customerDetails = [
            'first_name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone ?? '',
        ];

        $amount = $perpanjangan->tipe_pembayaran === 'Lunas' ? $perpanjangan->nominal_total : $perpanjangan->nominal_dp;
        $itemId = $perpanjangan->tipe_pembayaran === 'Lunas' ? 'LUNAS-EXT-'.$perpanjangan->id : 'DP-EXT-'.$perpanjangan->id;
        $itemName = $perpanjangan->tipe_pembayaran === 'Lunas' ?
            'Lunas Perpanjangan '.$perpanjangan->durasi_bulan.' Bln ('.($kamar->nomor_kamar ?? '').')' :
            'DP Perpanjangan '.$perpanjangan->durasi_bulan.' Bln ('.($kamar->nomor_kamar ?? '').')';

        $itemDetails = [
            [
                'id' => $itemId,
                'price' => (int) round($amount),
                'quantity' => 1,
                'name' => $itemName,
            ],
        ];

        try {
            $snapToken = $this->midtransService->createSnapToken(
                $orderId,
                $amount,
                $customerDetails,
                $itemDetails
            );
        } catch (\Throwable $e) {
            Log::error('Gagal membuat Snap Token untuk Perpanjangan', [
                'perpanjangan_id' => $perpanjangan->id,
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('penghuni.perpanjangan.show', $perpanjangan->id)
                ->with('error', 'Gagal memuat sistem pembayaran QRIS Midtrans. Silakan coba lagi.');
        }

        return view('penghuni.perpanjangan.bayar', compact('perpanjangan', 'snapToken', 'orderId'));
    }
}
