<?php

namespace App\Http\Controllers\Penghuni;

use App\Http\Controllers\Controller;
use App\Models\Keluhan;
use App\Models\RiwayatAktivitas;
use App\Models\Sewa;
use App\Notifications\BusinessNotification;
use App\Services\Notifications\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class KeluhanController extends Controller
{
    public function __construct(private readonly NotificationService $notificationService) {}

    private function getEligibleActiveSewa(): ?Sewa
    {
        $sewa = Sewa::with(['kamar', 'booking.pembayarans'])
            ->where('user_id', Auth::id())
            ->where('status', 'Aktif')
            ->latest()
            ->first();

        if (! $sewa) {
            return null;
        }

        $booking = $sewa->booking;
        $hasApprovedBooking = $booking && $booking->status === 'Selesai';
        $hasLunasPayment = $booking && $booking->pembayarans()->where('status', 'Lunas')->exists();

        if (! $hasApprovedBooking || ! $hasLunasPayment) {
            return null;
        }

        return $sewa;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');

        $query = Keluhan::with(['kamar', 'rating'])
            ->where('user_id', $user->id);

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $keluhans = $query->latest()->paginate(10)->withQueryString();

        return view('penghuni.keluhan.index', compact('keluhans', 'fromDate', 'toDate'));
    }

    public function create()
    {
        $activeSewa = $this->getEligibleActiveSewa();

        if (! $activeSewa) {
            return redirect()->route('penghuni.dashboard')
                ->with('error', 'Anda belum dapat menambahkan keluhan. Pastikan booking Anda telah disetujui, pembayaran sudah lunas, dan Anda memiliki sewa aktif.');
        }

        return view('penghuni.keluhan.create', compact('activeSewa'));
    }

    public function store(Request $request)
    {
        $activeSewa = $this->getEligibleActiveSewa();

        if (! $activeSewa) {
            return redirect()->route('penghuni.dashboard')
                ->with('error', 'Anda belum memenuhi syarat untuk mengirim keluhan. Pastikan booking Anda telah disetujui, pembayaran sudah lunas, dan sewa aktif sudah berjalan.');
        }

        $request->validate([
            'judul' => ['required', 'string', 'max:150'],
            'isi' => ['required', 'string', 'max:2000'],
            'foto' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:3072'],
        ], [
            'judul.required' => 'Judul keluhan atau laporan wajib diisi.',
            'isi.required' => 'Uraian keluhan wajib diisi dengan jelas.',
        ]);

        $path = null;

        if ($request->hasFile('foto')) {
            $path = $request->file('foto')->store('keluhan', 'public');
        }

        $kodeKeluhan = 'KLH-'.date('Ymd').'-'.strtoupper(Str::random(5));

        $keluhan = Keluhan::create([
            'kode_keluhan' => $kodeKeluhan,
            'user_id' => Auth::id(),
            'kamar_id' => $activeSewa->kamar_id,
            'judul' => $request->judul,
            'isi' => $request->isi,
            'foto' => $path,
            'status' => 'Menunggu',
        ]);

        RiwayatAktivitas::catat(
            Auth::id(),
            'Kirim Keluhan Fasilitas',
            'Mengirimkan laporan: "'.$request->judul.'" ('.$kodeKeluhan.')',
            'warning'
        );

        $notification = new BusinessNotification(
            'keluhan',
            'diajukan',
            'Keluhan baru diterima',
            'Keluhan '.$kodeKeluhan.' menunggu tindak lanjut.',
            'keluhan:'.$keluhan->id.':diajukan',
            ['entity_type' => 'keluhan', 'entity_id' => $keluhan->id, 'actor_id' => Auth::id()]
        );

        $this->notificationService->sendToRoleAfterCommit('pemilik-kost', $notification, Auth::id());
        $this->notificationService->sendToRoleAfterCommit('super-admin', $notification, Auth::id());

        return redirect()->route('penghuni.keluhan.index')
            ->with('success', 'Keluhan Anda berhasil dikirimkan. Pemilik kost akan segera menindaklanjuti.');
    }

    public function show($id)
    {
        $keluhan = Keluhan::with(['kamar', 'rating'])->where('user_id', Auth::id())->findOrFail($id);

        return view('penghuni.keluhan.show', compact('keluhan'));
    }
}
