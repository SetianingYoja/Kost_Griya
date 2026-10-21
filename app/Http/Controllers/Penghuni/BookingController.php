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

        $blockedMessage = null;

        try {
            $booking = DB::transaction(function () use ($request, &$blockedMessage) {
                // 1. Terapkan lockForUpdate() pada baris kamar sebelum memeriksa ketersediaan
                $kamar = Kamar::lockForUpdate()->findOrFail($request->kamar_id);

                if (! $kamar->isAvailableForBooking()) {
                    $blockedMessage = 'Kamar '.$kamar->nomor_kamar.' saat ini sedang '.$kamar->status.' dan tidak dapat dibooking.';

                    return null;
                }

                // 2. Lock user untuk validasi integritas akun penghuni
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
                    ->whereIn('status', ['Menunggu Pembayaran', 'Menunggu Validasi'])
                    ->where(function ($q) {
                        $q->whereNull('batas_pembayaran')
                            ->orWhere('batas_pembayaran', '>', Carbon::now());
                    })
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

                if ($tipePembayaran === 'Lunas') {
                    $nominalQris = (int) $totalHarga;
                    $nominalDp = 0;
                } else {
                    $nominalQris = (int) floor($kamar->harga * 0.30);
                    $nominalDp = $nominalQris;
                }

                $kodeBooking = 'BKG-'.date('Ymd').'-'.strtoupper(Str::random(5));
                $batasPembayaran = Carbon::now()->addMinutes(15);

                // Buat record booking langsung berstatus 'Menunggu Pembayaran' (tanpa perlu approval pemilik)
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
                    'status' => 'Menunggu Pembayaran',
                    'batas_pembayaran' => $batasPembayaran,
                ]);

                // Set status kamar menjadi 'Sedang Diproses' agar tidak dapat dibooking pengguna lain
                $kamar->status = 'Sedang Diproses';
                $kamar->save();

                $catatanRiwayat = $tipePembayaran === 'Lunas'
                    ? 'Membuat booking untuk kamar '.$kamar->nomor_kamar.' ('.$kodeBooking.') dengan pembayaran LUNAS via QRIS.'
                    : 'Membuat booking untuk kamar '.$kamar->nomor_kamar.' ('.$kodeBooking.') dengan DP QRIS.';

                RiwayatAktivitas::catat(
                    $user->id,
                    'Permohonan Booking Kamar',
                    $catatanRiwayat,
                    'info'
                );

                $notification = new BusinessNotification(
                    'booking',
                    'menunggu_pembayaran',
                    'Booking Baru Menunggu Pembayaran',
                    'Calon penghuni '.$user->name.' telah membuat booking kamar '.$kamar->nomor_kamar.' ('.$kodeBooking.') dan diarahkan ke pembayaran QRIS.',
                    'booking:'.$booking->id.':menunggu_pembayaran',
                    ['entity_type' => 'booking', 'entity_id' => $booking->id, 'actor_id' => $user->id]
                );

                $this->notificationService->sendToRoleAfterCommit('pemilik-kost', $notification, $user->id);
                $this->notificationService->sendToRoleAfterCommit('super-admin', $notification, $user->id);

                return $booking;
            });
        } catch (\Throwable $e) {
            Log::error('Gagal membuat booking kamar', [
                'user_id' => Auth::id(),
                'kamar_id' => $request->kamar_id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', $blockedMessage ?? 'Gagal membuat permohonan booking. Silakan coba beberapa saat lagi.');
        }

        if (! $booking) {
            return back()->with('error', $blockedMessage);
        }

        // Langsung arahkan ke halaman pembayaran QRIS Midtrans
        return redirect()->route('penghuni.booking.bayar', $booking->id)
            ->with('success', 'Booking berhasil dibuat! Silakan selesaikan pembayaran '.($booking->tipe_pembayaran === 'Lunas' ? 'Lunas' : 'DP').' via QRIS dalam waktu 60 menit.');
    }

    public function show($id)
    {
        $booking = Booking::with(['kamar.tipeKamar', 'pembayarans'])->where('user_id', Auth::id())->findOrFail($id);

        return view('penghuni.booking.show', compact('booking'));
    }

    public function showBayar($id)
    {
        $booking = Booking::with(['kamar.tipeKamar', 'user'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        if ($booking->status !== 'Menunggu Pembayaran') {
            return redirect()->route('penghuni.booking.show', $booking->id)
                ->with('info', 'Booking ini tidak dalam status menunggu pembayaran (Status: '.$booking->status.').');
        }

        // Check expiration
        if ($booking->batas_pembayaran && Carbon::now()->gt($booking->batas_pembayaran)) {
            DB::transaction(function () use ($booking) {
                $b = Booking::lockForUpdate()->find($booking->id);
                if ($b && $b->status === 'Menunggu Pembayaran') {
                    $hasPayment = $b->pembayarans()
                        ->whereIn('status', ['Menunggu Validasi', 'Lunas'])
                        ->exists();

                    if (! $hasPayment && ! in_array($b->midtrans_status, ['settlement', 'capture'], true)) {
                        $b->status = 'Kadaluarsa';
                        $b->save();

                        $kamar = Kamar::lockForUpdate()->find($b->kamar_id);
                        if ($kamar) {
                            $kamar->releaseIfAvailable($b->id);
                        }
                    }
                }
            });

            return redirect()->route('penghuni.booking.show', $booking->id)
                ->with('error', 'Waktu pembayaran QRIS untuk booking ini telah kedaluwarsa.');
        }

        // Generate Midtrans Snap token jika belum ada
        if (empty($booking->midtrans_snap_token) || empty($booking->midtrans_order_id)) {
            $orderId = $booking->midtrans_order_id ?? 'QRIS-'.$booking->kode_booking.'-'.time();
            $nominal = $booking->tipe_pembayaran === 'Lunas' ? (int) $booking->total_harga : (int) $booking->nominal_dp;
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
            $booking->midtrans_order_id = $orderId;
            $booking->midtrans_snap_token = $snapToken;
            $booking->midtrans_status = 'pending';
            $booking->save();
        }

        return view('penghuni.booking.bayar', compact('booking'));
    }

    public function cancel($id)
    {
        $blockedMessage = null;

        $cancelled = DB::transaction(function () use ($id, &$blockedMessage) {
            $booking = Booking::with('kamar')
                ->where('user_id', Auth::id())
                ->lockForUpdate()
                ->findOrFail($id);

            if (! in_array($booking->status, ['Menunggu Pembayaran', 'Menunggu Validasi'], true)) {
                $blockedMessage = 'Pemesanan dengan status ini tidak dapat dibatalkan.';

                return null;
            }

            // Pastikan tidak membatalkan booking yang sudah memiliki pembayaran valid/settlement
            $hasPayment = $booking->pembayarans()
                ->whereIn('status', ['Menunggu Validasi', 'Lunas'])
                ->exists();

            if ($hasPayment || in_array($booking->midtrans_status, ['settlement', 'capture'], true)) {
                $blockedMessage = 'Pemesanan yang telah memiliki pembayaran tidak dapat dibatalkan.';

                return null;
            }

            $booking->status = 'Dibatalkan';
            $booking->save();

            $kamar = Kamar::lockForUpdate()->find($booking->kamar_id);
            if ($kamar) {
                $kamar->releaseIfAvailable($booking->id);
            }

            return $booking;
        });

        if (! $cancelled) {
            return back()->with('error', $blockedMessage ?? 'Gagal membatalkan pemesanan.');
        }

        RiwayatAktivitas::catat(
            Auth::id(),
            'Booking Dibatalkan',
            'Membatalkan pemesanan kamar '.($cancelled->kamar->nomor_kamar ?? '-').' ('.$cancelled->kode_booking.')',
            'warning'
        );

        $notification = new BusinessNotification(
            'booking',
            'dibatalkan',
            'Booking dibatalkan',
            'Booking '.$cancelled->kode_booking.' dibatalkan oleh penghuni.',
            'booking:'.$cancelled->id.':dibatalkan',
            ['entity_type' => 'booking', 'entity_id' => $cancelled->id, 'actor_id' => Auth::id()]
        );

        $this->notificationService->sendToRoleAfterCommit('pemilik-kost', $notification, Auth::id());
        $this->notificationService->sendToRoleAfterCommit('super-admin', $notification, Auth::id());

        return redirect()->route('penghuni.dashboard')->with('success', 'Pemesanan kamar berhasil dibatalkan.');
    }
}
