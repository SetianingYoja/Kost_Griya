<?php

namespace App\Http\Controllers\Pemilik;

use App\Http\Controllers\Controller;
use App\Models\Rating;
use Illuminate\Http\Request;

class RatingController extends Controller
{
    public function index(Request $request)
    {
        $jenis = $request->query('jenis');
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');
        $query = Rating::with(['user', 'keluhan.kamar']);

        if ($jenis) {
            $query->where('jenis_rating', $jenis);
        }

        if ($fromDate) {
            $query->whereDate('created_at', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('created_at', '<=', $toDate);
        }

        $ratings = $query->latest()->paginate(10)->withQueryString();

        $avgKost = Rating::where('jenis_rating', 'Kost')->avg('skor') ?? 5.0;
        $avgKeluhan = Rating::where('jenis_rating', 'Penanganan Keluhan')->avg('skor') ?? 5.0;
        $totalRating = Rating::count();

        return view('pemilik.rating.index', compact('ratings', 'jenis', 'avgKost', 'avgKeluhan', 'totalRating', 'fromDate', 'toDate'));
    }
}
