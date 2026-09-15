<?php

namespace App\Http\Controllers\Penghuni;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Keluhan;
use App\Models\Rating;
use App\Models\RiwayatAktivitas;
use App\Models\Sewa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    private function getEligibleActiveSewa(): ?Sewa
    {
        $sewa = Sewa::with(['kamar', 'booking.pembayarans'])
            ->where('user_id', Auth::id())
            ->where('status', 'Aktif')
            ->latest()
            ->first();

        if (!$sewa) {
            return null;
        }

        $booking = $sewa->booking;
        $hasApprovedBooking = $booking && $booking->status === 'Selesai';
        $hasLunasPayment = $booking && $booking->pembayarans()->where('status', 'Lunas')->exists();

        if (!$hasApprovedBooking || !$hasLunasPayment) {
            return null;
        }

        return $sewa;
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');

        $query = Rating::with('keluhan')->where('user_id', $user->id);

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $ratings = $query->latest()->paginate(10)->withQueryString();
        $completedKeluhansWithoutRating = Keluhan::where('user_id', $user->id)
            ->where('status', 'Selesai')
            ->whereDoesntHave('rating')
            ->get();

        return view('penghuni.rating.index', compact('ratings', 'completedKeluhansWithoutRating', 'fromDate', 'toDate'));
    }

    public function create(Request $request)
    {
        $activeSewa = $this->getEligibleActiveSewa();

        if (!$activeSewa) {
            return redirect()->route('penghuni.dashboard')
                ->with('error', 'Anda belum dapat memberi rating. Pastikan booking Anda telah disetujui, pembayaran sudah lunas, dan Anda memiliki sewa aktif.');
        }

        $keluhan = null;
        if ($request->filled('keluhan_id')) {
            $keluhan = Keluhan::where('user_id', Auth::id())
                ->where('kamar_id', $activeSewa->kamar_id)
                ->where('status', 'Selesai')
                ->findOrFail($request->keluhan_id);
        }

        return view('penghuni.rating.create', compact('keluhan', 'activeSewa'));
    }

    public function store(Request $request)
    {
        $activeSewa = $this->getEligibleActiveSewa();

        if (!$activeSewa) {
            return redirect()->route('penghuni.dashboard')
                ->with('error', 'Anda belum memenuhi syarat untuk memberi rating. Pastikan booking Anda telah disetujui, pembayaran sudah lunas, dan sewa aktif sudah berjalan.');
        }

        $request->validate([
            'skor' => ['required', 'integer', 'min:1', 'max:5'],
            'komentar' => ['nullable', 'string', 'max:1000'],
            'jenis_rating' => ['required', 'in:Kost,Penanganan Keluhan'],
            'keluhan_id' => ['nullable', 'exists:keluhans,id'],
        ], [
            'skor.required' => 'Bintang penilaian (skor 1 - 5) wajib dipilih.',
            'skor.min' => 'Penilaian minimal 1 bintang.',
            'skor.max' => 'Penilaian maksimal 5 bintang.',
        ]);

        if ($request->keluhan_id) {
            $keluhan = Keluhan::where('user_id', Auth::id())
                ->where('kamar_id', $activeSewa->kamar_id)
                ->where('status', 'Selesai')
                ->findOrFail($request->keluhan_id);
        }

        $existing = Rating::where('user_id', Auth::id())
            ->where('sewa_id', $activeSewa->id)
            ->exists();

        if ($existing) {
            return redirect()->route('penghuni.rating.index')->with('info', 'Anda sudah pernah memberi rating untuk kamar/sewa aktif ini.');
        }

        $rating = Rating::create([
            'user_id' => Auth::id(),
            'kamar_id' => $activeSewa->kamar_id,
            'sewa_id' => $activeSewa->id,
            'keluhan_id' => $request->keluhan_id,
            'jenis_rating' => $request->jenis_rating,
            'skor' => $request->skor,
            'komentar' => $request->komentar,
        ]);

        RiwayatAktivitas::catat(
            Auth::id(),
            'Memberikan Rating',
            'Memberikan penilaian ' . $request->skor . ' bintang untuk ' . $request->jenis_rating,
            'success'
        );

        return redirect()->route('penghuni.rating.index')
            ->with('success', 'Terima kasih atas ulasan dan penilaian Anda untuk Kost Putri Griya Ayu!');
    }
}
