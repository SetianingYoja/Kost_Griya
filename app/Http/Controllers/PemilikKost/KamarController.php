<?php

namespace App\Http\Controllers\PemilikKost;

use App\Http\Controllers\Controller;
use App\Models\Kamar;
use App\Models\TipeKamar;
use Illuminate\Http\Request;

class KamarController extends Controller
{
    /**
     * Tampilkan daftar kamar (Read-Only).
     */
    public function index(Request $request)
    {
        $query = Kamar::with('tipeKamar');

        if ($request->filled('tipe')) {
            $query->where('tipe_kamar_id', $request->tipe);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('nomor_kamar', 'like', "%{$search}%")
                    ->orWhere('fasilitas', 'like', "%{$search}%");
            });
        }

        $kamars = $query->latest()->paginate(10)->withQueryString();
        $tipeKamars = TipeKamar::all();

        return view('pemilik-kost.kamar.index', compact('kamars', 'tipeKamars'));
    }

    /**
     * Tampilkan detail data kamar (Read-Only).
     */
    public function show($id)
    {
        $kamar = Kamar::with(['tipeKamar', 'sewas.user', 'bookings.user', 'keluhans'])->findOrFail($id);

        return view('pemilik-kost.kamar.show', compact('kamar'));
    }
}
