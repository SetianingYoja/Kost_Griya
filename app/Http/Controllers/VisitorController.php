<?php

namespace App\Http\Controllers;

use App\Models\Kamar;
use App\Models\KostInfo;
use App\Models\Rating;
use App\Models\TipeKamar;
use Illuminate\Http\Request;

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

        // Filter status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
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

        $kamars = $query->paginate(9)->withQueryString();
        $tipeKamars = TipeKamar::all();

        return view('visitor.kamar.index', compact('kamars', 'tipeKamars'));
    }

    public function kamarDetail($id)
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

        return view('visitor.kamar.detail', compact('kamar', 'kostInfo', 'kamarLain', 'kamarRatings', 'avgKamarRating', 'totalKamarReviews'));
    }

    public function tentang()
    {
        $kostInfo = KostInfo::first();
        $tipeKamars = TipeKamar::all();

        return view('visitor.tentang', compact('kostInfo', 'tipeKamars'));
    }
}
