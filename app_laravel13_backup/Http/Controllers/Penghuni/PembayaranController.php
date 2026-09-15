<?php

namespace App\Http\Controllers\Penghuni;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\KostInfo;
use App\Models\Pembayaran;
use App\Models\Perpanjangan;
use App\Models\RiwayatAktivitas;
use App\Models\Tagihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PembayaranController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $pembayarans = Pembayaran::with(['booking.kamar', 'tagihan', 'perpanjangan'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        return view('penghuni.pembayaran.index', compact('pembayarans'));
    }

    public function create(Request $request)
    {
        $kostInfo = KostInfo::first();
        $booking = null;
        $tagihan = null;
        $perpanjangan = null;
        $nominal = 0;
        $jenis = 'Booking Awal';

        if ($request->filled('booking_id')) {
            $booking = Booking::with('kamar')->where('user_id', Auth::id())->findOrFail($request->booking_id);
            $nominal = $booking->total_harga;
            $jenis = 'Booking Awal';
        } elseif ($request->filled('tagihan_id')) {
            $tagihan = Tagihan::with('kamar')->where('user_id', Auth::id())->findOrFail($request->tagihan_id);
            $nominal = $tagihan->total_bayar;
            $jenis = 'Tagihan Bulanan';
        } elseif ($request->filled('perpanjangan_id')) {
            $perpanjangan = Perpanjangan::with('sewa.kamar')->where('user_id', Auth::id())->findOrFail($request->perpanjangan_id);
            $nominal = $perpanjangan->nominal_dp;
            $jenis = 'DP Perpanjangan';
        } else {
            return redirect()->route('penghuni.dashboard')->with('error', 'Silakan pilih tagihan atau pesanan yang ingin dibayar.');
        }

        return view('penghuni.pembayaran.create', compact('kostInfo', 'booking', 'tagihan', 'perpanjangan', 'nominal', 'jenis'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'jenis_pembayaran' => ['required', 'in:Booking Awal,Tagihan Bulanan,DP Perpanjangan,Pelunasan Perpanjangan'],
            'nominal' => ['required', 'numeric', 'min:1000'],
            'nama_pengirim' => ['required', 'string', 'max:100'],
            'bank_pengirim' => ['required', 'string', 'max:50'],
            'tanggal_bayar' => ['required', 'date'],
            'bukti_pembayaran' => ['required', 'image', 'mimes:jpeg,png,jpg', 'max:3072'],
            'catatan_penghuni' => ['nullable', 'string', 'max:500'],
            'booking_id' => ['nullable', 'exists:bookings,id'],
            'tagihan_id' => ['nullable', 'exists:tagihans,id'],
            'perpanjangan_id' => ['nullable', 'exists:perpanjangans,id'],
        ], [
            'nama_pengirim.required' => 'Nama pemilik rekening pengirim wajib diisi.',
            'bank_pengirim.required' => 'Nama bank pengirim wajib diisi.',
            'tanggal_bayar.required' => 'Tanggal transfer wajib diisi.',
            'bukti_pembayaran.required' => 'Foto atau scan bukti transfer wajib diunggah.',
            'bukti_pembayaran.max' => 'Ukuran file bukti pembayaran maksimal 3 MB.',
        ]);

        $path = $request->file('bukti_pembayaran')->store('bukti_pembayaran', 'public');
        $kodePembayaran = 'PAY-' . date('Ymd') . '-' . strtoupper(Str::random(6));

        $pembayaran = Pembayaran::create([
            'kode_pembayaran' => $kodePembayaran,
            'user_id' => Auth::id(),
            'booking_id' => $request->booking_id,
            'tagihan_id' => $request->tagihan_id,
            'perpanjangan_id' => $request->perpanjangan_id,
            'jenis_pembayaran' => $validated['jenis_pembayaran'],
            'nominal' => $validated['nominal'],
            'metode_pembayaran' => 'Transfer Bank',
            'nama_pengirim' => $validated['nama_pengirim'],
            'bank_pengirim' => $validated['bank_pengirim'],
            'bukti_pembayaran' => $path,
            'tanggal_bayar' => $validated['tanggal_bayar'],
            'status' => 'Menunggu Validasi',
            'catatan_penghuni' => $request->catatan_penghuni,
        ]);

        // Perbarui status entitas terkait ke Menunggu Validasi
        if ($request->booking_id) {
            $booking = Booking::find($request->booking_id);
            if ($booking) {
                $booking->status = 'Menunggu Validasi';
                $booking->save();
            }
        }

        if ($request->tagihan_id) {
            $tagihan = Tagihan::find($request->tagihan_id);
            if ($tagihan) {
                $tagihan->status = 'Menunggu Validasi';
                $tagihan->save();
            }
        }

        if ($request->perpanjangan_id) {
            $perpanjangan = Perpanjangan::find($request->perpanjangan_id);
            if ($perpanjangan) {
                $perpanjangan->status = 'Menunggu Validasi';
                $perpanjangan->save();
            }
        }

        RiwayatAktivitas::catat(
            Auth::id(),
            'Upload Bukti Pembayaran',
            'Mengunggah bukti pembayaran ' . $validated['jenis_pembayaran'] . ' sebesar Rp ' . number_format($validated['nominal'], 0, ',', '.') . ' (' . $kodePembayaran . ')',
            'info'
        );

        return redirect()->route('penghuni.dashboard')
            ->with('success', 'Bukti pembayaran Anda berhasil diunggah! Mohon menunggu verifikasi dari Pemilik Kost.');
    }

    public function show($id)
    {
        $pembayaran = Pembayaran::with(['booking.kamar', 'tagihan', 'perpanjangan'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return view('penghuni.pembayaran.show', compact('pembayaran'));
    }
}
