<?php

namespace App\Http\Controllers\Penghuni;

use App\Http\Controllers\Controller;
use App\Models\Tagihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TagihanController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $tagihans = Tagihan::with(['kamar.tipeKamar', 'latestPembayaran'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        return view('penghuni.tagihan.index', compact('tagihans'));
    }

    public function show($id)
    {
        $tagihan = Tagihan::with(['kamar.tipeKamar', 'pembayarans', 'sewa'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return view('penghuni.tagihan.show', compact('tagihan'));
    }
}
