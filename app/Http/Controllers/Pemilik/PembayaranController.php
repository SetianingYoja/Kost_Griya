<?php

namespace App\Http\Controllers\Pemilik;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Pembayaran;
use App\Models\Perpanjangan;
use App\Models\RiwayatAktivitas;
use App\Models\Sewa;
use App\Models\Tagihan;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PembayaranController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $jenis = $request->query('jenis');
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');

        $query = Pembayaran::with(['user', 'booking.kamar', 'tagihan', 'perpanjangan']);

        if ($status) {
            $query->where('status', $status);
        }

        if ($jenis) {
            $query->where('jenis_pembayaran', $jenis);
        }

        if ($fromDate) {
            $query->whereDate('tanggal_bayar', '>=', $fromDate);
        }

        if ($toDate) {
            $query->whereDate('tanggal_bayar', '<=', $toDate);
        }

        $pembayarans = $query->latest()->paginate(10)->withQueryString();

        return view('pemilik.pembayaran.index', compact('pembayarans', 'status', 'jenis', 'fromDate', 'toDate'));
    }

    public function show($id)
    {
        $pembayaran = Pembayaran::with(['user', 'booking.kamar.tipeKamar', 'tagihan', 'perpanjangan'])->findOrFail($id);
        return view('pemilik.pembayaran.show', compact('pembayaran'));
    }

    public function approve(Request $request, $id)
    {
        $pembayaran = Pembayaran::with(['user', 'booking.kamar', 'tagihan', 'perpanjangan'])->findOrFail($id);

        if ($pembayaran->status === 'Lunas') {
            return back()->with('info', 'Pembayaran ini sudah berstatus Lunas.');
        }

        DB::transaction(function () use ($pembayaran, $request) {
            $pembayaran->status = 'Lunas';
            $pembayaran->diverifikasi_pada = Carbon::now();
            $pembayaran->catatan_pemilik = $request->input('catatan_pemilik', 'Pembayaran telah divalidasi dan diterima.');
            $pembayaran->save();

            // ALUR PEMBAYARAN BOOKING AWAL
            if ($pembayaran->jenis_pembayaran === 'Booking Awal' && $pembayaran->booking_id) {
                $booking = Booking::with('kamar')->find($pembayaran->booking_id);

                if ($booking) {
                    $booking->status = 'Selesai';
                    $booking->save();

                    // Kamar menjadi Terisi
                    $kamar = Kamar::find($booking->kamar_id);
                    if ($kamar) {
                        $kamar->status = 'Terisi';
                        $kamar->save();
                    }

                    // Buat Kontrak Sewa Aktif
                    $tanggalMulai = Carbon::parse($booking->tanggal_mulai);
                    $durasiBulan = (int) $booking->durasi_bulan;
                    $tanggalSelesai = $tanggalMulai->copy()->addMonths($durasiBulan)->subDay();

                    $sewa = Sewa::create([
                        'user_id' => $booking->user_id,
                        'kamar_id' => $booking->kamar_id,
                        'booking_id' => $booking->id,
                        'tanggal_mulai' => $tanggalMulai,
                        'tanggal_selesai' => $tanggalSelesai,
                        'status' => 'Aktif',
                        'harga_per_bulan' => $kamar ? $kamar->harga : 0,
                    ]);

                    // Buat Invoice Tagihan Pertama yang sudah lunas
                    $nomorTagihan = 'INV-' . date('Ymd') . '-' . strtoupper(Str::random(4));
                    Tagihan::create([
                        'nomor_tagihan' => $nomorTagihan,
                        'user_id' => $booking->user_id,
                        'kamar_id' => $booking->kamar_id,
                        'sewa_id' => $sewa->id,
                        'periode' => 'Bulan Pertama (' . $tanggalMulai->translatedFormat('F Y') . ')',
                        'bulan_ke' => 1,
                        'nominal' => $pembayaran->nominal,
                        'potongan_dp' => 0,
                        'total_bayar' => $pembayaran->nominal,
                        'tanggal_jatuh_tempo' => $tanggalMulai,
                        'status' => 'Lunas',
                    ]);

                    RiwayatAktivitas::catat(
                        $booking->user_id,
                        'Pembayaran Lunas & Sewa Aktif',
                        'Pembayaran booking awal lunas. Anda resmi aktif menjadi penghuni ' . ($kamar ? $kamar->nomor_kamar : 'kamar') . '.',
                        'success'
                    );
                }
            }

            // ALUR PEMBAYARAN TAGIHAN BULANAN
            if ($pembayaran->jenis_pembayaran === 'Tagihan Bulanan' && $pembayaran->tagihan_id) {
                $tagihan = Tagihan::find($pembayaran->tagihan_id);
                if ($tagihan) {
                    $tagihan->status = 'Lunas';
                    $tagihan->save();

                    RiwayatAktivitas::catat(
                        $tagihan->user_id,
                        'Tagihan Lunas',
                        'Tagihan periode ' . $tagihan->periode . ' (' . $tagihan->nomor_tagihan . ') telah divalidasi Lunas.',
                        'success'
                    );
                }
            }

            // ALUR PEMBAYARAN PERPANJANG SEWA (dp)
            if ($pembayaran->jenis_pembayaran === 'DP Perpanjangan' && $pembayaran->perpanjangan_id) {
                $perpanjangan = Perpanjangan::with('sewa')->find($pembayaran->perpanjangan_id);
                if ($perpanjangan) {
                    $perpanjangan->status = 'DP Dibayar';
                    $perpanjangan->save();

                    // Perpanjang masa sewa aktif
                    $sewa = $perpanjangan->sewa;
                    if ($sewa) {
                        $sewa->tanggal_selesai = $perpanjangan->tanggal_selesai_baru;
                        $sewa->status = 'Aktif';
                        $sewa->save();

                        // tagihan bulanan berjalan dengan alokasi DP terpotong agar tidak double charge
                        // total kewajiban dikurangi nominal DP yang sudah dibayar
                        $sisaKewajiban = $perpanjangan->nominal_total - $perpanjangan->nominal_dp;
                        $nominalPerBulan = $perpanjangan->durasi_bulan > 0 ? ($sisaKewajiban / $perpanjangan->durasi_bulan) : 0;

                        $nomorTagihan = 'INV-EXT-' . date('Ymd') . '-' . strtoupper(Str::random(4));
                        Tagihan::create([
                            'nomor_tagihan' => $nomorTagihan,
                            'user_id' => $perpanjangan->user_id,
                            'kamar_id' => $sewa->kamar_id,
                            'sewa_id' => $sewa->id,
                            'perpanjangan_id' => $perpanjangan->id,
                            'periode' => 'Perpanjangan (' . Carbon::parse($perpanjangan->tanggal_mulai_baru)->translatedFormat('F Y') . ')',
                            'bulan_ke' => 1,
                            'nominal' => $perpanjangan->nominal_total,
                            'potongan_dp' => $perpanjangan->nominal_dp,
                            'total_bayar' => $sisaKewajiban,
                            'tanggal_jatuh_tempo' => Carbon::parse($perpanjangan->tanggal_mulai_baru),
                            'status' => 'Belum Dibayar',
                        ]);
                    }

                    RiwayatAktivitas::catat(
                        $perpanjangan->user_id,
                        'DP Perpanjangan Lunas',
                        'DP perpanjangan sewa lunas. Masa sewa diperpanjang hingga ' . Carbon::parse($perpanjangan->tanggal_selesai_baru)->format('d M Y') . ' (Model B).',
                        'success'
                    );
                }
            }
        });

        return back()->with('success', 'Pembayaran berhasil divalidasi dan dinyatakan LUNAS!');
    }

    public function reject(Request $request, $id)
    {
        $request->validate([
            'catatan_pemilik' => ['required', 'string', 'max:500'],
        ], [
            'catatan_pemilik.required' => 'Mohon berikan alasan penolakan bukti pembayaran untuk penghuni.',
        ]);

        $pembayaran = Pembayaran::findOrFail($id);
        $pembayaran->status = 'Ditolak';
        $pembayaran->catatan_pemilik = $request->catatan_pemilik;
        $pembayaran->diverifikasi_pada = Carbon::now();
        $pembayaran->save();

        RiwayatAktivitas::catat(
            $pembayaran->user_id,
            'Pembayaran Ditolak',
            'Bukti pembayaran (' . $pembayaran->kode_pembayaran . ') ditolak: ' . $request->catatan_pemilik . '. Silakan unggah bukti yang benar.',
            'danger'
        );

        return back()->with('success', 'Pembayaran ditolak. Catatan perbaikan telah dikirimkan ke penghuni.');
    }
}
