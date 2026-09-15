<?php

namespace App\Http\Controllers\Penghuni;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Kamar;
use App\Models\RiwayatAktivitas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class BookingController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $bookings = Booking::with(['kamar.tipeKamar', 'latestPembayaran'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        return view('penghuni.booking.index', compact('bookings'));
    }

    public function create(Request $request)
    {
        $kamar = null;
        if ($request->filled('kamar_id')) {
            $kamar = Kamar::with('tipeKamar')->find($request->kamar_id);
        }

        $availableKamars = Kamar::with('tipeKamar')->where('status', 'Tersedia')->get();

        return view('penghuni.booking.create', compact('kamar', 'availableKamars'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kamar_id' => ['required', 'exists:kamar,id'],
            'tanggal_mulai' => ['required', 'date', 'after_or_equal:today'],
            'durasi_bulan' => ['required', 'integer', 'min:1', 'max:24'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ], [
            'kamar_id.required' => 'Silakan pilih kamar yang ingin dibooking.',
            'tanggal_mulai.required' => 'Tanggal mulai sewa wajib diisi.',
            'tanggal_mulai.after_or_equal' => 'Tanggal mulai sewa tidak boleh lewat dari hari ini.',
            'durasi_bulan.required' => 'Durasi sewa wajib dipilih.',
        ]);

        $kamar = Kamar::findOrFail($request->kamar_id);

        if ($kamar->status !== 'Tersedia') {
            return back()->with('error', 'Kamar yang Anda pilih saat ini sedang ' . $kamar->status . ' dan tidak dapat dibooking.');
        }

        // Cek apakah user sudah memiliki booking yang aktif
        $existingBooking = Booking::where('user_id', Auth::id())
            ->whereIn('status', ['Menunggu Validasi', 'Disetujui', 'Menunggu Pembayaran'])
            ->first();

        if ($existingBooking) {
            return redirect()->route('penghuni.dashboard')
                ->with('error', 'Anda masih memiliki pemesanan kamar yang sedang berlangsung (' . $existingBooking->kode_booking . '). Silakan selesaikan proses terlebih dahulu.');
        }

        $kodeBooking = 'BKG-' . date('Ymd') . '-' . strtoupper(Str::random(5));
        $totalHarga = $kamar->harga * $request->durasi_bulan;

        $booking = Booking::create([
            'kode_booking' => $kodeBooking,
            'user_id' => Auth::id(),
            'kamar_id' => $kamar->id,
            'tanggal_mulai' => $request->tanggal_mulai,
            'durasi_bulan' => $request->durasi_bulan,
            'total_harga' => $totalHarga,
            'catatan' => $request->catatan,
            'status' => 'Menunggu Validasi',
        ]);

        RiwayatAktivitas::catat(
            Auth::id(),
            'Permohonan Booking Kamar',
            'Mengajukan booking untuk ' . $kamar->nomor_kamar . ' dengan kode: ' . $kodeBooking,
            'info'
        );

        return redirect()->route('penghuni.dashboard')
            ->with('success', 'Permohonan booking kamar ' . $kamar->nomor_kamar . ' berhasil dibuat! Mohon menunggu validasi dari Pemilik Kost.');
    }

    public function show($id)
    {
        $booking = Booking::with(['kamar.tipeKamar', 'pembayarans'])->where('user_id', Auth::id())->findOrFail($id);

        return view('penghuni.booking.show', compact('booking'));
    }

    public function cancel($id)
    {
        $booking = Booking::where('user_id', Auth::id())->findOrFail($id);

        if (!in_array($booking->status, ['Menunggu Validasi', 'Disetujui', 'Menunggu Pembayaran'])) {
            return back()->with('error', 'Pemesanan dengan status ini tidak dapat dibatalkan.');
        }

        $booking->status = 'Dibatalkan';
        $booking->save();

        RiwayatAktivitas::catat(
            Auth::id(),
            'Booking Dibatalkan',
            'Membatalkan pemesanan kamar ' . $booking->kamar->nomor_kamar . ' (' . $booking->kode_booking . ')',
            'warning'
        );

        return redirect()->route('penghuni.dashboard')->with('success', 'Pemesanan kamar berhasil dibatalkan.');
    }
}
