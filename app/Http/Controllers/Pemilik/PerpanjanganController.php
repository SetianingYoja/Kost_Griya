<?php

namespace App\Http\Controllers\Pemilik;

use App\Http\Controllers\Controller;
use App\Models\Perpanjangan;
use App\Models\RiwayatAktivitas;
use Illuminate\Http\Request;

class PerpanjanganController extends Controller
{
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
        $perpanjangan = Perpanjangan::with(['user', 'sewa.kamar'])->findOrFail($id);

        $request->validate([
            'nominal_dp' => ['required', 'numeric', 'min:1000', 'max:' . $perpanjangan->nominal_total],
            'dp_persen' => ['required', 'integer', 'min:5', 'max:100'],
        ]);

        $perpanjangan->nominal_dp = $request->nominal_dp;
        $perpanjangan->dp_persen = $request->dp_persen;
        $perpanjangan->status = 'Menunggu Pembayaran DP';
        $perpanjangan->save();

        RiwayatAktivitas::catat(
            $perpanjangan->user_id,
            'Pengajuan Perpanjangan Disetujui',
            'Pengajuan perpanjangan disetujui. Silakan lakukan pembayaran DP sebesar Rp ' . number_format($request->nominal_dp, 0, ',', '.') . ' (' . $request->dp_persen . '%).',
            'success'
        );

        return back()->with('success', 'Pengajuan perpanjangan disetujui dengan kewajiban DP (Model B). Penghuni dapat mengunggah bukti DP.');
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
            'Pengajuan perpanjangan sewa ditolak: ' . $request->alasan_penolakan,
            'danger'
        );

        return back()->with('success', 'Pengajuan perpanjangan ditolak.');
    }
}
