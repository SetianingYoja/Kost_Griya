<?php

namespace App\Http\Controllers\Penghuni;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Kamar;
use App\Models\RiwayatAktivitas;
use App\Models\Sewa;
use App\Models\Tagihan;
use App\Notifications\BusinessNotification;
use App\Services\MidtransService;
use App\Services\Notifications\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BookingController extends Controller
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

        $query = Booking::with(['kamar.tipeKamar', 'latestPembayaran'])
            ->where('user_id', $user->id);

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $bookings = $query->latest()->paginate(10)->withQueryString();

        return view('penghuni.booking.index', compact('bookings', 'fromDate', 'toDate'));
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
            'tipe_pembayaran' => ['required', 'in:DP,Lunas'],
            'catatan' => ['nullable', 'string', 'max:500'],
        ], [
            'kamar_id.required' => 'Silakan pilih kamar yang ingin dibooking.',
            'tanggal_mulai.required' => 'Tanggal mulai sewa wajib diisi.',
            'tanggal_mulai.after_or_equal' => 'Tanggal mulai sewa tidak boleh lewat dari hari ini.',
            'durasi_bulan.required' => 'Durasi sewa wajib dipilih.',
            'tipe_pembayaran.required' => 'Silakan pilih tipe pembayaran.',
            'tipe_pembayaran.in' => 'Tipe pembayaran tidak valid.',
        ]);

        $kamar = Kamar::findOrFail($request->kamar_id);

        if ($kamar->status !== 'Tersedia') {
            return back()->with('error', 'Kamar yang Anda pilih saat ini sedang '.$kamar->status.' dan tidak dapat dibooking.');
        }

        $blockedMessage = null;

        try {
            $booking = DB::transaction(function () use ($request, $kamar, &$blockedMessage) {
                $user = Auth::user()->newQuery()->lockForUpdate()->findOrFail(Auth::id());

                $activeSewa = Sewa::where('user_id', $user->id)
                    ->where('status', 'Aktif')
                    ->with('kamar')
                    ->first();

                if ($activeSewa) {
                    $blockedMessage = 'Anda masih memiliki sewa aktif pada kamar '.($activeSewa->kamar->nomor_kamar ?? '-').'. Booking baru dapat dilakukan setelah Pemilik Kost mengonfirmasi sewa tersebut selesai.';

                    return null;
                }

                $existingBooking = Booking::where('user_id', $user->id)
                    ->whereIn('status', ['Menunggu Validasi', 'Disetujui', 'Menunggu Pembayaran'])
                    ->latest()
                    ->first();

                if ($existingBooking) {
                    $blockedMessage = 'Anda masih memiliki booking yang sedang diproses ('.$existingBooking->kode_booking.'). Silakan selesaikan proses tersebut terlebih dahulu.';

                    return null;
                }

                $unpaidTagihan = Tagihan::where('user_id', $user->id)
                    ->where('status', '!=', 'Lunas')
                    ->latest()
                    ->first();

                if ($unpaidTagihan) {
                    $blockedMessage = 'Anda masih memiliki tagihan belum lunas ('.$unpaidTagihan->nomor_tagihan.'). Silakan selesaikan seluruh tunggakan sebelum booking kamar baru.';

                    return null;
                }

                $tipePembayaran = $request->tipe_pembayaran; // 'DP' atau 'Lunas'
                $totalHarga = $kamar->harga * $request->durasi_bulan;

                // Hitung nominal QRIS berdasarkan tipe pembayaran:
                // DP  = 30% dari harga 1 bulan
                // Lunas = harga per bulan × durasi
                if ($tipePembayaran === 'Lunas') {
                    $nominalQris = (int) $totalHarga;
                    $nominalDp = 0; // tidak ada DP untuk tipe Lunas
                } else {
                    // DP: 30% dari harga 1 bulan
                    $nominalQris = (int) floor($kamar->harga * 0.30);
                    $nominalDp = $nominalQris;
                }

                $kodeBooking = 'BKG-'.date('Ymd').'-'.strtoupper(Str::random(5));
                $batasPembayaran = Carbon::now()->addMinutes(60);
                $orderId = 'QRIS-'.$kodeBooking.'-'.time();

                // Buat record booking tanpa QRIS karena masih menunggu validasi pemilik
                $booking = Booking::create([
                    'kode_booking' => $kodeBooking,
                    'user_id' => $user->id,
                    'kamar_id' => $kamar->id,
                    'tanggal_mulai' => $request->tanggal_mulai,
                    'durasi_bulan' => $request->durasi_bulan,
                    'tipe_pembayaran' => $tipePembayaran,
                    'total_harga' => $totalHarga,
                    'nominal_dp' => $nominalDp,
                    'catatan' => $request->catatan,
                    'status' => 'Menunggu Validasi',
                    'batas_pembayaran' => $batasPembayaran,
                ]);

                $catatanRiwayat = $tipePembayaran === 'Lunas'
                    ? 'Mengajukan booking untuk '.$kamar->nomor_kamar.' ('.$kodeBooking.') dengan pembayaran LUNAS via QRIS.'
                    : 'Mengajukan booking untuk '.$kamar->nomor_kamar.' ('.$kodeBooking.') dengan DP QRIS.';

                RiwayatAktivitas::catat(
                    $user->id,
                    'Permohonan Booking Kamar',
                    $catatanRiwayat,
                    'info'
                );

                return $booking;
            });
        } catch (\Throwable $e) {
            Log::error('Gagal membuat transaksi booking Midtrans Snap Token', [
                'user_id' => Auth::id(),
                'kamar_id' => $request->kamar_id,
                'error' => $e->getMessage(),
            ]);

            $errorMessage = $blockedMessage ?? 'Gagal membuat transaksi pembayaran QRIS Midtrans. Silakan coba beberapa saat lagi.';

            return redirect()->route('penghuni.dashboard')->with('error', $errorMessage);
        }

        if (! $booking) {
            return redirect()->route('penghuni.dashboard')->with('error', $blockedMessage);
        }

        $pesanSukses = $booking->tipe_pembayaran === 'Lunas'
            ? 'Booking kamar '.$kamar->nomor_kamar.' berhasil dibuat! Silakan lakukan pembayaran LUNAS via QRIS dalam waktu 60 menit.'
            : 'Booking kamar '.$kamar->nomor_kamar.' berhasil dibuat! Silakan lakukan pembayaran DP via QRIS dalam waktu 60 menit.';

        // Redirect to booking detail page with message that booking is awaiting validation
        return redirect()->route('penghuni.booking.show', $booking->id)
            ->with('success', 'Booking berhasil dibuat dan menunggu validasi oleh Pemilik Kost.');
    }

    public function show($id)
    {
        $booking = Booking::with(['kamar.tipeKamar', 'pembayarans'])->where('user_id', Auth::id())->findOrFail($id);

        return view('penghuni.booking.show', compact('booking'));
    }

    public function showBayar($id)
    {
        $booking = Booking::with(['kamar.tipeKamar'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        if ($booking->status !== 'Menunggu Pembayaran') {
            return redirect()->route('penghuni.booking.show', $booking->id)
                ->with('info', 'Booking ini tidak dalam status menunggu pembayaran (Status: '.$booking->status.').');
        }

        // Check expiration
        if ($booking->batas_pembayaran && Carbon::now()->gt($booking->batas_pembayaran)) {
            $booking->status = 'Kadaluarsa';
            $booking->save();

            return redirect()->route('penghuni.booking.show', $booking->id)
                ->with('error', 'Waktu pembayaran QRIS untuk booking ini telah kedaluwarsa.');
        }

        // Ensure Midtrans token exists after approval
        if (empty($booking->midtrans_snap_token) || empty($booking->midtrans_order_id)) {
            // Generate order ID if not present
            $orderId = $booking->midtrans_order_id ?? 'QRIS-'.$booking->kode_booking.'-'.time();
            // Determine nominal amount based on tipe pembayaran
            $nominal = $booking->tipe_pembayaran === 'Lunas' ? $booking->total_harga : $booking->nominal_dp;
            $customerDetails = [
                'first_name' => $booking->user->name ?? '',
                'email' => $booking->user->email ?? '',
                'phone' => $booking->user->no_telepon ?? '',
            ];
            $itemDetails = [];
            if ($booking->tipe_pembayaran === 'Lunas') {
                $itemDetails = [[
                    'id' => 'LUNAS-'.$booking->kamar->id,
                    'price' => $nominal,
                    'quantity' => 1,
                    'name' => 'Booking Lunas Kamar '.$booking->kamar->nomor_kamar.' ('.$booking->durasi_bulan.' Bulan)',
                ]];
            } else {
                $itemDetails = [[
                    'id' => 'DP-'.$booking->kamar->id,
                    'price' => $nominal,
                    'quantity' => 1,
                    'name' => 'DP Booking Kamar '.$booking->kamar->nomor_kamar.' (30%)',
                ]];
            }
            $snapToken = $this->midtransService->createSnapToken($orderId, $nominal, $customerDetails, $itemDetails);
            // Save token and order info
            $booking->midtrans_order_id = $orderId;
            $booking->midtrans_snap_token = $snapToken;
            $booking->midtrans_status = 'pending';
            $booking->save();
        }

        return view('penghuni.booking.bayar', compact('booking'));
    }

    public function cancel($id)
    {
        $booking = Booking::where('user_id', Auth::id())->findOrFail($id);

        if (! in_array($booking->status, ['Menunggu Validasi', 'Disetujui', 'Menunggu Pembayaran'])) {
            return back()->with('error', 'Pemesanan dengan status ini tidak dapat dibatalkan.');
        }

        $booking->status = 'Dibatalkan';
        $booking->save();

        RiwayatAktivitas::catat(
            Auth::id(),
            'Booking Dibatalkan',
            'Membatalkan pemesanan kamar '.$booking->kamar->nomor_kamar.' ('.$booking->kode_booking.')',
            'warning'
        );

        $notification = new BusinessNotification(
            'booking',
            'dibatalkan',
            'Booking dibatalkan',
            'Booking '.$booking->kode_booking.' dibatalkan oleh penghuni.',
            'booking:'.$booking->id.':dibatalkan',
            ['entity_type' => 'booking', 'entity_id' => $booking->id, 'actor_id' => Auth::id()]
        );

        $this->notificationService->sendToRoleAfterCommit('pemilik-kost', $notification, Auth::id());
        $this->notificationService->sendToRoleAfterCommit('super-admin', $notification, Auth::id());

        return redirect()->route('penghuni.dashboard')->with('success', 'Pemesanan kamar berhasil dibatalkan.');
    }
}
