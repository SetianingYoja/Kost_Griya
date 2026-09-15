<?php

namespace App\Http\Controllers\Penghuni;

use App\Http\Controllers\Controller;
use App\Models\Tagihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TagihanController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');

        $query = Tagihan::with(['kamar.tipeKamar', 'latestPembayaran'])
            ->where('user_id', $user->id);

        if ($fromDate) {
            $query->whereDate('tanggal_jatuh_tempo', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('tanggal_jatuh_tempo', '<=', $toDate);
        }

        $tagihans = $query->latest()->paginate(10)->withQueryString();

        return view('penghuni.tagihan.index', compact('tagihans', 'fromDate', 'toDate'));
    }

    public function show($id)
    {
        $tagihan = Tagihan::with(['kamar.tipeKamar', 'pembayarans', 'sewa'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return view('penghuni.tagihan.show', compact('tagihan'));
    }
}
