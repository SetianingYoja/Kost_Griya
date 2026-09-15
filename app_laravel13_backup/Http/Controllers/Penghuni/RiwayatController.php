<?php

namespace App\Http\Controllers\Penghuni;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Keluhan;
use App\Models\Pembayaran;
use App\Models\Rating;
use App\Models\Sewa;
use App\Models\Tagihan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RiwayatController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $tab = $request->query('tab', 'sewa');

        $sewas = Sewa::with('kamar.tipeKamar')->where('user_id', $user->id)->latest()->get();
        $bookings = Booking::with('kamar.tipeKamar')->where('user_id', $user->id)->latest()->get();
        $pembayarans = Pembayaran::where('user_id', $user->id)->latest()->get();
        $tagihans = Tagihan::with('kamar')->where('user_id', $user->id)->latest()->get();
        $keluhans = Keluhan::with('kamar')->where('user_id', $user->id)->latest()->get();
        $ratings = Rating::where('user_id', $user->id)->latest()->get();

        return view('penghuni.riwayat.index', compact(
            'user',
            'tab',
            'sewas',
            'bookings',
            'pembayarans',
            'tagihans',
            'keluhans',
            'ratings'
        ));
    }
}
