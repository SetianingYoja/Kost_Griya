<?php

namespace App\Http\Controllers\Penghuni;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Services\MidtransService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TagihanController extends Controller
{
    public function __construct(
        private readonly MidtransService $midtransService
    ) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');

        $query = Tagihan::with(['kamar.tipeKamar', 'latestPembayaran'])
            ->where('user_id', $user->id);

        if ($fromDate) {
            $query->whereDate('tanggal_jatuh_tempo', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('tanggal_jatuh_tempo', '<=', $toDate);
        }

        $tagihans = $query->latest()->paginate(10)->withQueryString();

        return view('penghuni.tagihan.index', compact('tagihans', 'fromDate', 'toDate'));
    }

    public function show($id)
    {
        $tagihan = Tagihan::with(['kamar.tipeKamar', 'pembayarans', 'sewa'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return view('penghuni.tagihan.show', compact('tagihan'));
    }

    public function showBayar($id)
    {
        $tagihan = Tagihan::with(['kamar.tipeKamar', 'sewa'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        // Hanya tagihan yang belum lunas yang dapat dibayar
        if ($tagihan->status === 'Lunas') {
            return redirect()->route('penghuni.tagihan.show', $tagihan->id)
                ->with('info', 'Tagihan ini sudah lunas.');
        }

        // Cek apakah ada pembayaran QRIS aktif yang sedang menunggu validasi
        $existingPembayaran = Pembayaran::where('tagihan_id', $tagihan->id)
            ->where('jenis_pembayaran', 'Tagihan Bulanan')
            ->where('status', 'Menunggu Validasi')
            ->whereNotNull('midtrans_transaction_id')
            ->first();

        if ($existingPembayaran) {
            return redirect()->route('penghuni.tagihan.show', $tagihan->id)
                ->with('info', 'Pembayaran QRIS untuk tagihan ini sudah diterima dan sedang menunggu validasi Pemilik Kost.');
        }

        $user = Auth::user();
        $kamar = $tagihan->kamar;
        $orderId = 'BILL-'.$tagihan->id.'-'.time();

        $customerDetails = [
            'first_name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone ?? '',
        ];

        $itemDetails = [
            [
                'id' => 'TAGIHAN-'.$tagihan->id,
                'price' => (int) round($tagihan->total_bayar),
                'quantity' => 1,
                'name' => 'Tagihan '.($tagihan->periode ?? 'Bulan').' ('.($kamar->nomor_kamar ?? '').')',
            ],
        ];

        try {
            $snapToken = $this->midtransService->createSnapToken(
                $orderId,
                $tagihan->total_bayar,
                $customerDetails,
                $itemDetails
            );
        } catch (\Throwable $e) {
            Log::error('Gagal membuat Snap Token untuk Tagihan Bulanan', [
                'tagihan_id' => $tagihan->id,
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return redirect()->route('penghuni.tagihan.show', $tagihan->id)
                ->with('error', 'Gagal memuat sistem pembayaran QRIS Midtrans. Silakan coba lagi.');
        }

        return view('penghuni.tagihan.bayar', compact('tagihan', 'snapToken', 'orderId'));
    }
}
