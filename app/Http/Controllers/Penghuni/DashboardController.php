<?php

namespace App\Http\Controllers\Penghuni;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Keluhan;
use App\Models\KostInfo;
use App\Models\Pembayaran;
use App\Models\Perpanjangan;
use App\Models\Sewa;
use App\Models\Tagihan;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $kostInfo = KostInfo::first();

        // Pastikan relasi booking berstatus Aktif terhubung dengan benar
        Booking::ensureActiveBookingIntegrity();

        // 1. Cek apakah memiliki sewa aktif
        $activeSewa = Sewa::with(['kamar.tipeKamar'])
            ->where('user_id', $user->id)
            ->where('status', 'Aktif')
            ->latest()
            ->first();

        // 2. Cek apakah ada booking aktif / sedang berjalan
        $activeBooking = Booking::with('kamar.tipeKamar')
            ->where('user_id', $user->id)
            ->whereNotIn('status', ['Kadaluarsa', 'Dibatalkan', 'Selesai'])
            ->latest()
            ->first();

        // Tentukan Tenant Life-cycle State
        $state = 'belum_booking';

        if ($activeSewa) {
            $state = 'aktif_menyewa';
        } elseif ($activeBooking) {
            if ($activeBooking->status === 'Menunggu Validasi') {
                $state = 'menunggu_validasi_booking';
            } elseif ($activeBooking->status === 'Menunggu Pembayaran' || $activeBooking->status === 'Disetujui') {
                $state = 'menunggu_pembayaran';
            } elseif ($activeBooking->status === 'Ditolak') {
                $state = 'booking_ditolak';
            } elseif ($activeBooking->status === 'Aktif') {
                $state = 'aktif_menyewa';
            }
        } else {
            // Cek apakah pernah sewa tapi sudah berakhir
            $pastSewa = Sewa::where('user_id', $user->id)->where('status', 'Selesai')->exists();
            if ($pastSewa) {
                $state = 'masa_sewa_berakhir';
            }
        }

        // Data pendukung
        $tagihans = Tagihan::where('user_id', $user->id)->latest()->take(5)->get();
        $unpaidTagihans = Tagihan::where('user_id', $user->id)->whereIn('status', ['Belum Dibayar', 'Terlambat'])->get();
        $pembayarans = Pembayaran::where('user_id', $user->id)->latest()->take(5)->get();
        $keluhans = Keluhan::where('user_id', $user->id)->latest()->take(5)->get();
        $pendingKeluhanForRating = Keluhan::where('user_id', $user->id)
            ->where('status', 'Selesai')
            ->whereDoesntHave('rating')
            ->latest()
            ->first();

        $activePerpanjangan = Perpanjangan::where('user_id', $user->id)
            ->whereIn('status', ['Menunggu Validasi', 'Disetujui', 'Menunggu Pembayaran DP'])
            ->latest()
            ->first();

        return view('penghuni.dashboard', compact(
            'user',
            'kostInfo',
            'state',
            'activeSewa',
            'activeBooking',
            'tagihans',
            'unpaidTagihans',
            'pembayarans',
            'keluhans',
            'pendingKeluhanForRating',
            'activePerpanjangan'
        ));
    }
}
