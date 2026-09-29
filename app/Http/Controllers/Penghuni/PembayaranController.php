<?php

namespace App\Http\Controllers\Penghuni;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\KostInfo;
use App\Models\Pembayaran;
use App\Models\Perpanjangan;
use App\Models\RiwayatAktivitas;
use App\Models\Tagihan;
use App\Notifications\BusinessNotification;
use App\Services\Notifications\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PembayaranController extends Controller
{
    public function __construct(private readonly NotificationService $notificationService) {}

    public function index(Request $request)
    {
        $user = Auth::user();
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');

        $query = Pembayaran::with(['booking.kamar', 'tagihan', 'perpanjangan'])
            ->where('user_id', $user->id);

        if ($fromDate) {
            $query->whereDate('tanggal_bayar', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('tanggal_bayar', '<=', $toDate);
        }

        $pembayarans = $query->latest()->paginate(10)->withQueryString();

        return view('penghuni.pembayaran.index', compact('pembayarans', 'fromDate', 'toDate'));
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

            return redirect()->route('penghuni.booking.bayar', $booking->id)
                ->with('info', 'Pembayaran booking awal dilakukan secara instan via QRIS Midtrans.');
        } elseif ($request->filled('tagihan_id')) {
            $tagihan = Tagihan::with('kamar')->where('user_id', Auth::id())->findOrFail($request->tagihan_id);

            return redirect()->route('penghuni.tagihan.bayar', $tagihan->id)
                ->with('info', 'Pembayaran tagihan bulanan dilakukan via QRIS Midtrans.');
        } elseif ($request->filled('perpanjangan_id')) {
            $perpanjangan = Perpanjangan::with('sewa.kamar')->where('user_id', Auth::id())->findOrFail($request->perpanjangan_id);
            $isLunas = $perpanjangan->tipe_pembayaran === 'Lunas';
            $nominal = $isLunas ? $perpanjangan->nominal_total : $perpanjangan->nominal_dp;
            $jenis = $isLunas ? 'Pelunasan Perpanjangan' : 'DP Perpanjangan';
        } else {
            return redirect()->route('penghuni.dashboard')->with('error', 'Silakan pilih tagihan atau pesanan yang ingin dibayar.');
        }

        return view('penghuni.pembayaran.create', compact('kostInfo', 'booking', 'tagihan', 'perpanjangan', 'nominal', 'jenis'));
    }

    public function store(Request $request)
    {
        if ($request->jenis_pembayaran === 'Booking Awal' || $request->filled('booking_id')) {
            return redirect()->route('penghuni.dashboard')
                ->with('error', 'Pembayaran booking awal dilakukan via QRIS Midtrans dan tidak menerima unggahan bukti transfer manual.');
        }

        if ($request->jenis_pembayaran === 'Tagihan Bulanan' || $request->filled('tagihan_id')) {
            $tagihanId = $request->tagihan_id;
            if ($tagihanId) {
                return redirect()->route('penghuni.tagihan.bayar', $tagihanId)
                    ->with('info', 'Pembayaran tagihan bulanan dilakukan via QRIS Midtrans.');
            }

            return redirect()->route('penghuni.tagihan.index')
                ->with('error', 'Pembayaran tagihan bulanan dilakukan via QRIS Midtrans.');
        }

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
        $kodePembayaran = 'PAY-'.date('Ymd').'-'.strtoupper(Str::random(6));

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
            'Mengunggah bukti pembayaran '.$validated['jenis_pembayaran'].' sebesar Rp '.number_format($validated['nominal'], 0, ',', '.').' ('.$kodePembayaran.')',
            'info'
        );

        $notification = new BusinessNotification(
            'pembayaran',
            'diunggah',
            'Bukti pembayaran baru',
            'Bukti pembayaran '.$kodePembayaran.' menunggu validasi.',
            'pembayaran:'.$pembayaran->id.':diunggah',
            ['entity_type' => 'pembayaran', 'entity_id' => $pembayaran->id, 'actor_id' => Auth::id()]
        );

        $this->notificationService->sendToRoleAfterCommit('pemilik-kost', $notification, Auth::id());
        $this->notificationService->sendToRoleAfterCommit('super-admin', $notification, Auth::id());

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
