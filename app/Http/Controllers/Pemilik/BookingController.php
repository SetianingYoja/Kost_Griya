<?php

namespace App\Http\Controllers\Pemilik;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\RiwayatAktivitas;
use App\Notifications\BusinessNotification;
use App\Services\Notifications\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function __construct(private readonly NotificationService $notificationService) {}

    public function index(Request $request)
    {
        $status = $request->query('status');
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');
        $query = Booking::with(['user', 'kamar.tipeKamar', 'latestPembayaran']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $bookings = $query->latest()->paginate(10)->withQueryString();

        return view('pemilik.booking.index', compact('bookings', 'status', 'fromDate', 'toDate'));
    }

    public function show($id)
    {
        $booking = Booking::with(['user', 'kamar.tipeKamar', 'pembayarans'])->findOrFail($id);
        $hasInitialPaymentAwaitingValidation = $this->hasInitialPaymentAwaitingValidation($booking);

        return view('pemilik.booking.show', compact('booking', 'hasInitialPaymentAwaitingValidation'));
    }

    public function approve(Request $request, $id)
    {
        $booking = Booking::with(['user', 'kamar'])->findOrFail($id);

        if ($booking->status !== 'Menunggu Validasi') {
            return back()->with('error', 'Booking ini sudah tidak dalam status menunggu validasi.');
        }

        if ($this->hasInitialPaymentAwaitingValidation($booking)) {
            return back()->with('error', 'Pembayaran Booking Awal sudah menunggu validasi. Silakan lanjutkan melalui menu Validasi Pembayaran.');
        }

        $booking->status = 'Menunggu Pembayaran';
        $booking->batas_pembayaran = Carbon::now()->addHours(24);
        $booking->save();

        RiwayatAktivitas::catat(
            $booking->user_id,
            'Booking Disetujui Pemilik',
            'Permohonan booking untuk '.$booking->kamar->nomor_kamar.' disetujui. Silakan lakukan pembayaran dalam 24 jam.',
            'success'
        );

        $notification = new BusinessNotification(
            'booking',
            'disetujui',
            'Booking disetujui',
            'Booking '.$booking->kode_booking.' disetujui dan menunggu pembayaran.',
            'booking:'.$booking->id.':disetujui',
            ['entity_type' => 'booking', 'entity_id' => $booking->id, 'actor_id' => Auth::id()]
        );

        $this->notificationService->sendAfterCommit($booking->user, $notification);
        $this->notificationService->sendToRoleAfterCommit('super-admin', $notification, Auth::id());

        return back()->with('success', 'Booking disetujui! Status berubah menjadi Menunggu Pembayaran.');
    }

    private function hasInitialPaymentAwaitingValidation(Booking $booking): bool
    {
        return $booking->pembayarans()
            ->where('jenis_pembayaran', 'Booking Awal')
            ->where(function ($query) use ($booking) {
                $query->where('status', 'Menunggu Validasi');

                if (in_array($booking->midtrans_status, ['settlement', 'capture'], true)) {
                    $query->orWhere('metode_pembayaran', 'QRIS');
                }
            })
            ->exists();
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'alasan_penolakan' => ['required', 'string', 'max:500'],
        ], [
            'alasan_penolakan.required' => 'Alasan penolakan booking wajib diisi.',
        ]);

        $booking = Booking::with(['user', 'kamar'])->findOrFail($id);

        if ($booking->status !== 'Menunggu Validasi') {
            return back()->with('error', 'Booking ini sudah tidak dalam status menunggu validasi.');
        }

        $booking->status = 'Ditolak';
        $booking->alasan_penolakan = $request->alasan_penolakan;
        $booking->save();

        RiwayatAktivitas::catat(
            $booking->user_id,
            'Booking Ditolak',
            'Permohonan booking kamar '.$booking->kamar->nomor_kamar.' ditolak: '.$request->alasan_penolakan,
            'danger'
        );

        $notification = new BusinessNotification(
            'booking',
            'ditolak',
            'Booking ditolak',
            'Booking '.$booking->kode_booking.' ditolak: '.$booking->alasan_penolakan,
            'booking:'.$booking->id.':ditolak',
            ['entity_type' => 'booking', 'entity_id' => $booking->id, 'actor_id' => Auth::id()]
        );

        $this->notificationService->sendAfterCommit($booking->user, $notification);
        $this->notificationService->sendToRoleAfterCommit('super-admin', $notification, Auth::id());

        return back()->with('success', 'Booking berhasil ditolak dengan alasan yang tercatat.');
    }
}
