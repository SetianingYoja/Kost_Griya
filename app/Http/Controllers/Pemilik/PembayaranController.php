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
use App\Notifications\BusinessNotification;
use App\Services\Notifications\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PembayaranController extends Controller
{
    public function __construct(private readonly NotificationService $notificationService) {}

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

                    $hargaPerBulan = $kamar ? (float) $kamar->harga : 0;
                    $tipePembayaran = $booking->tipe_pembayaran ?? 'DP';

                    if ($tipePembayaran === 'Lunas') {
                        // BOOKING LUNAS: Buat tagihan per bulan untuk seluruh durasi sewa
                        // Semua tagihan langsung berstatus Lunas (tidak ada outstanding)
                        // IDEMPOTENCY: Cek apakah tagihan untuk booking ini sudah dibuat
                        $existingTagihanCount = Tagihan::where('sewa_id', $sewa->id)->count();

                        if ($existingTagihanCount === 0) {
                            for ($i = 1; $i <= $durasiBulan; $i++) {
                                $nomorTagihan = 'INV-'.date('Ymd').'-'.strtoupper(Str::random(4)).'-'.$i;
                                $jatuhTempoBulan = $tanggalMulai->copy()->addMonths($i - 1);
                                $namaPeriode = ($i === 1)
                                    ? 'Bulan Pertama ('.$jatuhTempoBulan->translatedFormat('F Y').')'
                                    : 'Bulan '.$i.' ('.$jatuhTempoBulan->translatedFormat('F Y').')';

                                Tagihan::create([
                                    'nomor_tagihan' => $nomorTagihan,
                                    'user_id' => $booking->user_id,
                                    'kamar_id' => $booking->kamar_id,
                                    'sewa_id' => $sewa->id,
                                    'periode' => $namaPeriode,
                                    'bulan_ke' => $i,
                                    'nominal' => $hargaPerBulan,
                                    'potongan_dp' => 0,
                                    'total_bayar' => 0, // Sudah dibayar lunas via booking
                                    'tanggal_jatuh_tempo' => $jatuhTempoBulan,
                                    'status' => 'Lunas', // Langsung lunas karena sudah bayar penuh
                                ]);
                            }
                        }

                        RiwayatAktivitas::catat(
                            $booking->user_id,
                            'Pembayaran Lunas & Sewa Aktif',
                            'Pembayaran booking LUNAS divalidasi. Anda resmi aktif menjadi penghuni '.($kamar ? $kamar->nomor_kamar : 'kamar').' dan seluruh tagihan '.$durasiBulan.' bulan telah dilunasi.',
                            'success'
                        );
                    } else {
                        // BOOKING DP: Buat tagihan per bulan untuk seluruh durasi sewa
                        // IDEMPOTENCY: Cek apakah tagihan untuk booking ini sudah dibuat
                        $existingTagihanCount = Tagihan::where('sewa_id', $sewa->id)->count();

                        if ($existingTagihanCount === 0) {
                            $nominalDp = (float) $booking->nominal_dp;

                            for ($i = 1; $i <= $durasiBulan; $i++) {
                                $nomorTagihan = 'INV-'.date('Ymd').'-'.strtoupper(Str::random(4)).'-'.$i;
                                $jatuhTempoBulan = $tanggalMulai->copy()->addMonths($i - 1);
                                $namaPeriode = ($i === 1)
                                    ? 'Bulan Pertama ('.$jatuhTempoBulan->translatedFormat('F Y').')'
                                    : 'Bulan '.$i.' ('.$jatuhTempoBulan->translatedFormat('F Y').')';

                                $potonganBulanIni = ($i === 1) ? $nominalDp : 0;
                                $totalBayarBulanIni = max(0, $hargaPerBulan - $potonganBulanIni);

                                Tagihan::create([
                                    'nomor_tagihan' => $nomorTagihan,
                                    'user_id' => $booking->user_id,
                                    'kamar_id' => $booking->kamar_id,
                                    'sewa_id' => $sewa->id,
                                    'periode' => $namaPeriode,
                                    'bulan_ke' => $i,
                                    'nominal' => $hargaPerBulan,
                                    'potongan_dp' => $potonganBulanIni,
                                    'total_bayar' => $totalBayarBulanIni,
                                    'tanggal_jatuh_tempo' => $jatuhTempoBulan,
                                    'status' => 'Belum Dibayar',
                                ]);
                            }
                        }

                        RiwayatAktivitas::catat(
                            $booking->user_id,
                            'Pembayaran DP & Sewa Aktif',
                            'Pembayaran DP booking awal divalidasi. Anda resmi aktif menjadi penghuni '.($kamar ? $kamar->nomor_kamar : 'kamar').'.',
                            'success'
                        );
                    }
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
                        'Tagihan periode '.$tagihan->periode.' ('.$tagihan->nomor_tagihan.') telah divalidasi Lunas.',
                        'success'
                    );
                }
            }

            // ALUR PEMBAYARAN PERPANJANG SEWA (DP ATAU LUNAS)
            if (in_array($pembayaran->jenis_pembayaran, ['DP Perpanjangan', 'Pelunasan Perpanjangan', 'Lunas Perpanjangan']) && $pembayaran->perpanjangan_id) {
                $perpanjangan = Perpanjangan::with(['sewa.kamar'])->lockForUpdate()->find($pembayaran->perpanjangan_id);

                if (! $perpanjangan) {
                    throw new \Exception('Data pengajuan perpanjangan tidak ditemukan.');
                }

                $sewa = $perpanjangan->sewa;
                if (! $sewa) {
                    throw new \Exception('Data kontrak sewa aktif terkait perpanjangan tidak ditemukan.');
                }

                // Validasi kelengkapan data perpanjangan
                $durasiBulan = (int) $perpanjangan->durasi_bulan;
                $hargaPerBulan = (float) ($sewa->harga_per_bulan ?? 0);
                $nominalTotal = (float) $perpanjangan->nominal_total;
                $nominalDp = (float) $perpanjangan->nominal_dp;
                $tanggalMulaiBaru = $perpanjangan->tanggal_mulai_baru ? Carbon::parse($perpanjangan->tanggal_mulai_baru) : null;
                $tanggalSelesaiBaru = $perpanjangan->tanggal_selesai_baru ? Carbon::parse($perpanjangan->tanggal_selesai_baru) : null;

                $isLunas = ($perpanjangan->tipe_pembayaran === 'Lunas') || in_array($pembayaran->jenis_pembayaran, ['Pelunasan Perpanjangan', 'Lunas Perpanjangan']);

                if ($durasiBulan <= 0 || $hargaPerBulan <= 0 || $nominalTotal <= 0 || (! $isLunas && $nominalDp <= 0) || ! $tanggalMulaiBaru || ! $tanggalSelesaiBaru) {
                    throw new \Exception('Data perpanjangan atau tarif sewa tidak valid untuk proses aktivasi.');
                }

                // 1. Status akhir perpanjangan setelah validasi adalah "Aktif"
                $perpanjangan->status = 'Aktif';
                $perpanjangan->save();

                // 2. Perpanjang masa sewa aktif menggunakan tanggal_selesai_baru
                $sewa->tanggal_selesai = $tanggalSelesaiBaru;
                $sewa->status = 'Aktif';
                $sewa->save();

                // 3. IDEMPOTENCY: Cek apakah Tagihan untuk perpanjangan ini sudah pernah dibuat sebelumnya
                $existingTagihanCount = Tagihan::where('perpanjangan_id', $perpanjangan->id)->count();

                if ($existingTagihanCount === 0) {
                    if ($isLunas) {
                        // PERPANJANGAN LUNAS: Buat tagihan per bulan untuk seluruh durasi, status langsung Lunas
                        for ($i = 1; $i <= $durasiBulan; $i++) {
                            $nomorTagihan = 'INV-EXT-'.date('Ymd').'-'.strtoupper(Str::random(4)).'-'.$i;
                            $jatuhTempoBulan = $tanggalMulaiBaru->copy()->addMonths($i - 1);
                            $namaPeriode = 'Perpanjangan ('.$jatuhTempoBulan->translatedFormat('F Y').')';

                            Tagihan::create([
                                'nomor_tagihan' => $nomorTagihan,
                                'user_id' => $perpanjangan->user_id,
                                'kamar_id' => $sewa->kamar_id,
                                'sewa_id' => $sewa->id,
                                'perpanjangan_id' => $perpanjangan->id,
                                'periode' => $namaPeriode,
                                'bulan_ke' => $i,
                                'nominal' => $hargaPerBulan,
                                'potongan_dp' => 0,
                                'total_bayar' => 0, // Sudah dibayar lunas
                                'tanggal_jatuh_tempo' => $jatuhTempoBulan,
                                'status' => 'Lunas',
                            ]);
                        }
                    } else {
                        // PERPANJANGAN DP: Buat tagihan per bulan sesuai durasi perpanjangan
                        for ($i = 1; $i <= $durasiBulan; $i++) {
                            $nomorTagihan = 'INV-EXT-'.date('Ymd').'-'.strtoupper(Str::random(4)).'-'.$i;

                            // Jatuh tempo: bulan ke-1 mulai dari tanggal_mulai_baru, bulan ke-N ditambah (i-1) bulan
                            $jatuhTempoBulan = $tanggalMulaiBaru->copy()->addMonths($i - 1);
                            $namaPeriode = 'Perpanjangan ('.$jatuhTempoBulan->translatedFormat('F Y').')';

                            // Aturan DP: Potongan DP hanya diterapkan pada bulan ke-1
                            $potonganBulanIni = ($i === 1) ? $nominalDp : 0;
                            $totalBayarBulanIni = max(0, $hargaPerBulan - $potonganBulanIni);

                            Tagihan::create([
                                'nomor_tagihan' => $nomorTagihan,
                                'user_id' => $perpanjangan->user_id,
                                'kamar_id' => $sewa->kamar_id,
                                'sewa_id' => $sewa->id,
                                'perpanjangan_id' => $perpanjangan->id,
                                'periode' => $namaPeriode,
                                'bulan_ke' => $i,
                                'nominal' => $hargaPerBulan,
                                'potongan_dp' => $potonganBulanIni,
                                'total_bayar' => $totalBayarBulanIni,
                                'tanggal_jatuh_tempo' => $jatuhTempoBulan,
                                'status' => ($totalBayarBulanIni <= 0) ? 'Lunas' : 'Belum Dibayar',
                            ]);
                        }
                    }
                }

                $nomorKamar = $sewa->kamar->nomor_kamar ?? 'kamar';
                $isQrisPayment = $pembayaran->metode_pembayaran === 'QRIS';
                $metodeInfo = $isQrisPayment ? 'QRIS' : 'Transfer';
                $labelTipe = $isLunas ? 'Lunas' : 'DP';

                RiwayatAktivitas::catat(
                    $perpanjangan->user_id,
                    'Perpanjangan Sewa Aktif',
                    'Pembayaran '.$labelTipe.' Perpanjangan '.$metodeInfo.' kamar '.$nomorKamar.' telah divalidasi dan masa sewa berhasil diperpanjang hingga '.$tanggalSelesaiBaru->format('d M Y').'.',
                    'success'
                );
            }
        });

        if ($pembayaran->jenis_pembayaran === 'Tagihan Bulanan') {
            $notificationTitle = 'Pembayaran Tagihan Disetujui';
            $notificationMessage = 'Pembayaran Tagihan Bulanan '.($pembayaran->metode_pembayaran === 'QRIS' ? 'QRIS ' : '').$pembayaran->kode_pembayaran.' telah divalidasi dan Tagihan telah dilunasi.';
            $successMessage = 'Pembayaran Tagihan Bulanan berhasil divalidasi dan dinyatakan LUNAS!';
        } elseif (in_array($pembayaran->jenis_pembayaran, ['DP Perpanjangan', 'Pelunasan Perpanjangan', 'Lunas Perpanjangan'])) {
            $labelTipe = in_array($pembayaran->jenis_pembayaran, ['Pelunasan Perpanjangan', 'Lunas Perpanjangan']) ? 'Pelunasan' : 'DP';
            $notificationTitle = 'Pembayaran Disetujui';
            $notificationMessage = 'Pembayaran '.$labelTipe.' Perpanjangan '.($pembayaran->metode_pembayaran === 'QRIS' ? 'QRIS ' : '').$pembayaran->kode_pembayaran.' telah divalidasi dan masa sewa berhasil diperpanjang.';
            $successMessage = 'Pembayaran berhasil divalidasi dan masa sewa perpanjangan resmi AKTIF!';
        } else {
            $notificationTitle = 'Pembayaran Disetujui';
            $notificationMessage = 'Pembayaran '.$pembayaran->kode_pembayaran.' telah divalidasi dan dinyatakan lunas.';
            $successMessage = 'Pembayaran berhasil divalidasi dan dinyatakan LUNAS!';
        }

        $notification = new BusinessNotification(
            'pembayaran',
            'disetujui',
            $notificationTitle,
            $notificationMessage,
            'pembayaran:'.$pembayaran->id.':disetujui',
            ['entity_type' => 'pembayaran', 'entity_id' => $pembayaran->id, 'actor_id' => Auth::id()]
        );

        $this->notificationService->sendAfterCommit($pembayaran->user, $notification);
        $this->notificationService->sendToRoleAfterCommit('super-admin', $notification, Auth::id());

        return back()->with('success', $successMessage);
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
            'Bukti pembayaran ('.$pembayaran->kode_pembayaran.') ditolak: '.$request->catatan_pemilik.'. Silakan unggah bukti yang benar.',
            'danger'
        );

        $notification = new BusinessNotification(
            'pembayaran',
            'ditolak',
            'Pembayaran ditolak',
            'Pembayaran '.$pembayaran->kode_pembayaran.' ditolak: '.$pembayaran->catatan_pemilik,
            'pembayaran:'.$pembayaran->id.':ditolak',
            ['entity_type' => 'pembayaran', 'entity_id' => $pembayaran->id, 'actor_id' => Auth::id()]
        );

        $this->notificationService->sendAfterCommit($pembayaran->user, $notification);
        $this->notificationService->sendToRoleAfterCommit('super-admin', $notification, Auth::id());

        return back()->with('success', 'Pembayaran ditolak. Catatan perbaikan telah dikirimkan ke penghuni.');
    }
}
