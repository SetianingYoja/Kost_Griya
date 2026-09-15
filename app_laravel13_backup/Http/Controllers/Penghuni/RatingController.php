<?php

namespace App\Http\Controllers\Penghuni;

use App\Http\Controllers\Controller;
use App\Models\Keluhan;
use App\Models\Rating;
use App\Models\RiwayatAktivitas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RatingController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $ratings = Rating::with('keluhan')->where('user_id', $user->id)->latest()->paginate(10);
        $completedKeluhansWithoutRating = Keluhan::where('user_id', $user->id)
            ->where('status', 'Selesai')
            ->whereDoesntHave('rating')
            ->get();

        return view('penghuni.rating.index', compact('ratings', 'completedKeluhansWithoutRating'));
    }

    public function create(Request $request)
    {
        $keluhan = null;
        if ($request->filled('keluhan_id')) {
            $keluhan = Keluhan::where('user_id', Auth::id())
                ->where('status', 'Selesai')
                ->findOrFail($request->keluhan_id);
        }

        return view('penghuni.rating.create', compact('keluhan'));
    }

    public function store(Request $request)
    {
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
                ->where('status', 'Selesai')
                ->findOrFail($request->keluhan_id);

            // Cek apakah sudah pernah dinilai
            $existing = Rating::where('keluhan_id', $keluhan->id)->first();
            if ($existing) {
                return redirect()->route('penghuni.rating.index')->with('info', 'Keluhan ini sudah pernah diberi penilaian.');
            }
        }

        $rating = Rating::create([
            'user_id' => Auth::id(),
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
