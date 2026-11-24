<?php

namespace App\Http\Controllers;

use App\Models\Kamar;
use App\Models\KostInfo;
use App\Models\Rating;
use App\Models\TipeKamar;
use Illuminate\Http\Request;
use Carbon\Carbon;

class VisitorController extends Controller
{
    public function home()
    {
        $kostInfo = KostInfo::first();
        $tipeKamars = TipeKamar::withCount('kamars')->get();
        $kamarUnggulan = Kamar::with('tipeKamar')->latest()->take(6)->get();
        $totalTersedia = Kamar::where('status', 'Tersedia')->count();
        $totalKamar = Kamar::count();

        $recentRatings = Rating::with(['user', 'kamar'])
            ->validForDisplay()
            ->where('jenis_rating', 'Kost')
            ->latest()
            ->take(4)
            ->get();

        $avgRating = Rating::validForDisplay()->where('jenis_rating', 'Kost')->avg('skor') ?? 0;
        $totalReviews = Rating::validForDisplay()->where('jenis_rating', 'Kost')->count();

        return view('visitor.home', compact(
            'kostInfo',
            'tipeKamars',
            'kamarUnggulan',
            'totalTersedia',
            'totalKamar',
            'recentRatings',
            'avgRating',
            'totalReviews'
        ));
    }

    public function kamarIndex(Request $request)
    {
        $query = Kamar::with('tipeKamar');

        // Filter tipe
        if ($request->filled('tipe')) {
            $query->where('tipe_kamar_id', $request->tipe);
        }


        // Filter lantai
        if ($request->filled('lantai')) {
            $query->where('lantai', $request->lantai);
        }

        // Search nomor kamar / fasilitas
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_kamar', 'like', "%{$search}%")
                    ->orWhere('fasilitas', 'like', "%{$search}%")
                    ->orWhere('deskripsi', 'like', "%{$search}%");
            });
        }

        $isDurationFilterActive = $request->filled('start_date') && $request->filled('duration');

        // Filter status statis hanya digunakan jika filter tanggal TIDAK diisi
        if ($request->filled('status') && ! $isDurationFilterActive) {
            $query->where('status', $request->status);
        }

        // Filter by availability period
        if ($isDurationFilterActive) {
            $start = \Carbon\Carbon::parse($request->start_date)->startOfDay();
            $duration = (int) $request->duration;
            $end = (clone $start)->addMonths($duration)->endOfDay();

            if ($start->gt($end)) {
                return back()->withErrors(['duration' => 'Durasi harus lebih dari 0 dan tanggal selesai harus setelah tanggal mulai.']);
            }


            // Kamar yang secara fisik 'Tidak tersedia' (rusak/maintenance) tidak boleh muncul
            $query->where('status', '!=', 'Tidak tersedia');

            // Exclude rooms with active leases overlapping the period
            $query->whereDoesntHave('sewas', function ($q) use ($start, $end) {
                $q->where('status', 'Aktif')
                    ->where('tanggal_mulai', '<=', $end)
                    ->where('tanggal_selesai', '>=', $start);
            });

            // Exclude rooms with bookings that occupy the period
            // Hindari pemblokiran ganda dengan mengecualikan booking yang sudah memiliki kontrak sewa
            $query->whereDoesntHave('bookings', function ($q) use ($start, $end) {
                $q->whereDoesntHave('sewa')
                    ->where(function ($statusQuery) {
                        $statusQuery->whereIn('status', ['Menunggu Validasi', 'Disetujui', 'Aktif'])
                            ->orWhere(function ($pendingQuery) {
                                $pendingQuery->where('status', 'Menunggu Pembayaran')
                                    ->where(function ($bpQuery) {
                                        $bpQuery->whereNull('batas_pembayaran')
                                            ->orWhere('batas_pembayaran', '>', \Carbon\Carbon::now());
                                    });
                            });
                    })
                    ->where(function ($sub) use ($start, $end) {
                        $sub->where('tanggal_mulai', '<=', $end);
                        if (\Illuminate\Support\Facades\DB::connection()->getDriverName() === 'sqlite') {
                            $sub->whereRaw("date(tanggal_mulai, '+' || durasi_bulan || ' month') >= ?", [$start->toDateString()]);
                        } else {
                            $sub->whereRaw('DATE_ADD(tanggal_mulai, INTERVAL durasi_bulan MONTH) >= ?', [$start]);
                        }
                    });
            });
        }

        $kamars = $query->paginate(9)->withQueryString();


        $tipeKamars = TipeKamar::all();

        return view('visitor.kamar.index', compact('kamars', 'tipeKamars'));
    }

    public function kamarDetail(Request $request, $id)
    {
        $kamar = Kamar::with('tipeKamar')->findOrFail($id);
        $kostInfo = KostInfo::first();
        $kamarLain = Kamar::with('tipeKamar')
            ->where('id', '!=', $id)
            ->where('status', 'Tersedia')
            ->take(3)
            ->get();

        $kamarRatings = Rating::with('user')
            ->validForDisplay()
            ->where('kamar_id', $kamar->id)
            ->where('jenis_rating', 'Kost')
            ->latest()
            ->get();

        $avgKamarRating = $kamarRatings->avg('skor') ?? 0;
        $totalKamarReviews = $kamarRatings->count();

        // Determine booking availability based on query parameters
        $canBook = false;
        $startDate = $request->query('start_date');
        $duration = $request->query('duration');
        if ($startDate && $duration && $kamar->effective_status !== 'Tidak tersedia') {
            $start = \Carbon\Carbon::parse($startDate)->startOfDay();
            $end = (clone $start)->addMonths((int) $duration)->endOfDay();

            $leaseOverlap = $kamar->sewas()
                ->where('status', 'Aktif')
                ->where('tanggal_mulai', '<=', $end)
                ->where('tanggal_selesai', '>=', $start)
                ->exists();

            $bookingOverlap = $kamar->bookings()
                ->whereIn('status', ['Menunggu Pembayaran', 'Menunggu Validasi', 'Aktif'])
                ->where(function ($q) {
                    $q->whereNull('batas_pembayaran')
                      ->orWhere('batas_pembayaran', '>', \Carbon\Carbon::now());
                })
                ->where(function ($q) use ($start, $end) {
                    $q->where('tanggal_mulai', '<=', $end);
                    if (\Illuminate\Support\Facades\DB::connection()->getDriverName() === 'sqlite') {
                        $q->whereRaw("date(tanggal_mulai, '+' || durasi_bulan || ' month') >= ?", [$start->toDateString()]);
                    } else {
                        $q->whereRaw('DATE_ADD(tanggal_mulai, INTERVAL durasi_bulan MONTH) >= ?', [$start]);
                    }
                })
                ->exists();

            $canBook = !($leaseOverlap || $bookingOverlap);
        }

        return view('visitor.kamar.detail', compact('kamar', 'kostInfo', 'kamarLain', 'kamarRatings', 'avgKamarRating', 'totalKamarReviews', 'canBook', 'startDate', 'duration'));
    }

    public function ulasanIndex(Request $request)
    {
        $kostInfo = KostInfo::first();

        $baseQuery = Rating::with(['user', 'kamar'])
            ->validForDisplay()
            ->where('jenis_rating', 'Kost');

        $totalReviews = (clone $baseQuery)->count();
        $avgRating = (clone $baseQuery)->avg('skor') ?? 0;

        $starCounts = [
            5 => (clone $baseQuery)->where('skor', 5)->count(),
            4 => (clone $baseQuery)->where('skor', 4)->count(),
            3 => (clone $baseQuery)->where('skor', 3)->count(),
            2 => (clone $baseQuery)->where('skor', 2)->count(),
            1 => (clone $baseQuery)->where('skor', 1)->count(),
        ];

        $filterRating = $request->query('rating');
        if ($filterRating && in_array((int) $filterRating, [1, 2, 3, 4, 5], true)) {
            $baseQuery->where('skor', (int) $filterRating);
        }

        $ratings = $baseQuery->latest()->paginate(12);

        return view('visitor.ulasan.index', compact(
            'kostInfo',
            'ratings',
            'totalReviews',
            'avgRating',
            'starCounts',
            'filterRating'
        ));
    }

    public function tentang()
    {
        $kostInfo = KostInfo::first();
        $tipeKamars = TipeKamar::all();

        return view('visitor.tentang', compact('kostInfo', 'tipeKamars'));
    }
}
