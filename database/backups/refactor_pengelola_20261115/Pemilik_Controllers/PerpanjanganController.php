<?php

namespace App\Http\Controllers\Pemilik;

use App\Http\Controllers\Controller;
use App\Models\Perpanjangan;
use App\Models\RiwayatAktivitas;
use App\Notifications\BusinessNotification;
use App\Services\Notifications\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PerpanjanganController extends Controller
{
    public function __construct(private readonly NotificationService $notificationService) {}

    public function index(Request $request)
    {
        $status = $request->query('status');
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');
        $query = Perpanjangan::with(['user', 'sewa.kamar', 'dpPembayaran']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $perpanjangans = $query->latest()->paginate(10)->withQueryString();

        return view('pemilik.perpanjangan.index', compact('perpanjangans', 'status', 'fromDate', 'toDate'));
    }

    public function show($id)
    {
        $perpanjangan = Perpanjangan::with(['user', 'sewa.kamar', 'dpPembayaran', 'tagihans'])->findOrFail($id);

        return view('pemilik.perpanjangan.show', compact('perpanjangan'));
    }

    public function approve(Request $request, $id)
    {
        $perpanjangan = Perpanjangan::with(['user', 'sewa.kamar', 'dpPembayaran'])->findOrFail($id);

        if ($perpanjangan->status !== 'Menunggu Validasi') {
            return back()->with('info', 'Pengajuan perpanjangan ini sudah tidak dalam status menunggu persetujuan.');
        }

        if ($perpanjangan->dpPembayaran && $perpanjangan->dpPembayaran->status === 'Menunggu Validasi') {
            return back()->with('info', 'Pembayaran perpanjangan ini sudah masuk dan sedang menunggu validasi pembayaran.');
        }

        $hargaSewa1Bulan = $perpanjangan->sewa->harga_per_bulan ?? 0;

        if ($perpanjangan->tipe_pembayaran === 'Lunas') {
            // Lunas: nominal pembayaran = total seluruh durasi
            $perpanjangan->nominal_dp = 0;
            $perpanjangan->dp_persen = 0;
        } else {
            // DP: 30% dari harga sewa 1 bulan
            $nominalDp = floor($hargaSewa1Bulan * 0.30);
            $perpanjangan->nominal_dp = $nominalDp;
            $perpanjangan->dp_persen = 30;
        }

        $perpanjangan->status = 'Menunggu Pembayaran DP';
        $perpanjangan->save();

        $labelTipe = $perpanjangan->tipe_pembayaran === 'Lunas' ? 'LUNAS' : 'DP';
        $nominalBayar = $perpanjangan->tipe_pembayaran === 'Lunas'
            ? $perpanjangan->nominal_total
            : $perpanjangan->nominal_dp;

        RiwayatAktivitas::catat(
            $perpanjangan->user_id,
            'Pengajuan Perpanjangan Disetujui',
            'Pengajuan perpanjangan disetujui. Silakan lakukan pembayaran '.$labelTipe.' sebesar Rp '.number_format($nominalBayar, 0, ',', '.').'.',
            'success'
        );

        $notification = new BusinessNotification(
            'perpanjangan',
            'disetujui',
            'Perpanjangan disetujui',
            'Pengajuan perpanjangan disetujui dengan pembayaran '.$labelTipe.' sebesar Rp '.number_format($nominalBayar, 0, ',', '.').'.',
            'perpanjangan:'.$perpanjangan->id.':disetujui',
            ['entity_type' => 'perpanjangan', 'entity_id' => $perpanjangan->id, 'actor_id' => Auth::id()]
        );

        $this->notificationService->sendAfterCommit($perpanjangan->user, $notification);
        $this->notificationService->sendToRoleAfterCommit('super-admin', $notification, Auth::id());

        return back()->with('success', 'Pengajuan perpanjangan disetujui ('.$labelTipe.'). Penghuni dapat melakukan pembayaran.');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'alasan_penolakan' => ['required', 'string', 'max:500'],
        ]);

        $perpanjangan = Perpanjangan::with('user')->findOrFail($id);
        $perpanjangan->status = 'Ditolak';
        $perpanjangan->alasan_penolakan = $request->alasan_penolakan;
        $perpanjangan->save();

        RiwayatAktivitas::catat(
            $perpanjangan->user_id,
            'Pengajuan Perpanjangan Ditolak',
            'Pengajuan perpanjangan sewa ditolak: '.$request->alasan_penolakan,
            'danger'
        );

        $notification = new BusinessNotification(
            'perpanjangan',
            'ditolak',
            'Perpanjangan ditolak',
            'Pengajuan perpanjangan ditolak: '.$perpanjangan->alasan_penolakan,
            'perpanjangan:'.$perpanjangan->id.':ditolak',
            ['entity_type' => 'perpanjangan', 'entity_id' => $perpanjangan->id, 'actor_id' => Auth::id()]
        );

        $this->notificationService->sendAfterCommit($perpanjangan->user, $notification);
        $this->notificationService->sendToRoleAfterCommit('super-admin', $notification, Auth::id());

        return back()->with('success', 'Pengajuan perpanjangan ditolak.');
    }
}
