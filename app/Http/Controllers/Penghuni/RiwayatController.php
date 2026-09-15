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
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');

        $sewas = Sewa::with('kamar.tipeKamar')->where('user_id', $user->id);
        $bookings = Booking::with('kamar.tipeKamar')->where('user_id', $user->id);
        $pembayarans = Pembayaran::where('user_id', $user->id);
        $tagihans = Tagihan::with('kamar')->where('user_id', $user->id);
        $keluhans = Keluhan::with('kamar')->where('user_id', $user->id);
        $ratings = Rating::where('user_id', $user->id);

        foreach (['sewas', 'bookings', 'pembayarans', 'tagihans', 'keluhans', 'ratings'] as $collectionName) {
            $query = $$collectionName;

            if ($fromDate) {
                $query->whereDate('created_at', '>=', $fromDate);
            }

            if ($toDate) {
                $query->whereDate('created_at', '<=', $toDate);
            }

            $query->latest();
            $$collectionName = $query->get();
        }

        return view('penghuni.riwayat.index', compact(
            'user',
            'tab',
            'sewas',
            'bookings',
            'pembayarans',
            'tagihans',
            'keluhans',
            'ratings',
            'fromDate',
            'toDate'
        ));
    }
}
