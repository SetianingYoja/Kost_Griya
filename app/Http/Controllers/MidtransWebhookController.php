<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Kamar;
use App\Models\Pembayaran;
use App\Models\Perpanjangan;
use App\Models\RiwayatAktivitas;
use App\Models\Sewa;
use App\Models\Tagihan;
use App\Notifications\BusinessNotification;
use App\Services\MidtransService;
use App\Services\Notifications\NotificationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class MidtransWebhookController extends Controller
{
    public function __construct(
        private readonly MidtransService $midtransService,
        private readonly NotificationService $notificationService
    ) {}

    /**
     * Menangani Payment Notification Webhook dari Midtrans.
     */
    public function handleNotification(Request $request): JsonResponse
    {
        $payload = $request->all();

        // 1. Logging aman tanpa menyertakan secret key / token sensitif
        Log::info('Midtrans Webhook: Notification received', [
            'order_id' => $payload['order_id'] ?? null,
            'transaction_status' => $payload['transaction_status'] ?? null,
            'payment_type' => $payload['payment_type'] ?? null,
            'gross_amount' => $payload['gross_amount'] ?? null,
            'status_code' => $payload['status_code'] ?? null,
            'transaction_id' => $payload['transaction_id'] ?? null,
        ]);

        $orderId = $payload['order_id'] ?? null;
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $inputSignature = $payload['signature_key'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? null;
        $transactionId = $payload['transaction_id'] ?? null;
        $paymentType = $payload['payment_type'] ?? 'qris';

        if (! $orderId || ! $statusCode || ! $grossAmount || ! $inputSignature) {
            Log::warning('Midtrans Webhook: Missing required signature fields', [
                'order_id' => $orderId,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Missing required notification signature fields',
            ], 400);
        }

        // 2. Verifikasi Signature Key (order_id + status_code + gross_amount + server_key)
        if (! $this->midtransService->isValidSignature($orderId, $statusCode, $grossAmount, $inputSignature)) {
            Log::warning('Midtrans Webhook: Invalid signature key detected!', [
                'order_id' => $orderId,
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Invalid signature key',
            ], 401);
        }

        $isSuccessStatus = $transactionStatus === 'settlement' || ($transactionStatus === 'capture' && $fraudStatus === 'accept');

        // 5. Konsistensi transaksi database dengan pencegahan race condition
        try {
            $response = null;

            DB::transaction(function () use (
                $orderId,
                $payload,
                $transactionStatus,
                $isSuccessStatus,
                $transactionId,
                $paymentType,
                $grossAmount,
                &$response
            ) {
                // Cek tipe transaksi berdasarkan awalan order_id atau prefix
                $isPerpanjanganOrder = str_starts_with($orderId, 'EXT-');

                if ($isPerpanjanganOrder) {
                    // ALUR DP PERPANJANGAN QRIS
                    // Format order_id: EXT-{perpanjangan_id}-{timestamp}
                    $orderParts = explode('-', $orderId);
                    $perpanjanganId = $orderParts[1] ?? null;

                    $perpanjangan = Perpanjangan::with(['sewa.kamar', 'user'])
                        ->where('id', $perpanjanganId)
                        ->lockForUpdate()
                        ->first();

                    if (! $perpanjangan) {
                        Log::warning('Midtrans Webhook: Perpanjangan not found for order_id', [
                            'order_id' => $orderId,
                            'perpanjangan_id' => $perpanjanganId,
                        ]);
                        $response = response()->json([
                            'status' => 'error',
                            'message' => 'Perpanjangan not found',
                        ], 404);

                        return;
                    }

                    // Pengujian Idempotensi: jika Pembayaran untuk transaksi ini sudah ada
                    $existingPembayaran = Pembayaran::where('perpanjangan_id', $perpanjangan->id)
                        ->where(function ($query) use ($transactionId, $orderId) {
                            $query->where('midtrans_transaction_id', $transactionId)
                                ->orWhere('catatan_penghuni', 'like', '%'.$orderId.'%');
                        })
                        ->first();

                    if ($existingPembayaran && in_array($existingPembayaran->status, ['Menunggu Validasi', 'Lunas'])) {
                        // Pencegahan downgrade status: jika sudah diproses, jangan diubah oleh notifikasi tertunda
                        if (! $isSuccessStatus) {
                            Log::info('Midtrans Webhook: Ignoring delayed non-success status for perpanjangan', [
                                'order_id' => $orderId,
                                'received_status' => $transactionStatus,
                                'pembayaran_status' => $existingPembayaran->status,
                            ]);
                            $response = response()->json([
                                'status' => 'success',
                                'message' => 'Ignored delayed webhook',
                            ], 200);

                            return;
                        }

                        Log::info('Midtrans Webhook: Notification already processed (idempotent for perpanjangan)', [
                            'order_id' => $orderId,
                            'perpanjangan_id' => $perpanjangan->id,
                            'pembayaran_id' => $existingPembayaran->id,
                        ]);
                        $response = response()->json([
                            'status' => 'success',
                            'message' => 'Notification already processed (idempotent)',
                        ], 200);

                        return;
                    }

                    if ($isSuccessStatus) {
                        $paidAt = isset($payload['settlement_time'])
                            ? Carbon::parse($payload['settlement_time'])
                            : Carbon::now();

                        // Update status perpanjangan menjadi "Menunggu Validasi"
                        // JANGAN mengaktifkan sewa, JANGAN mengubah perpanjangan ke Aktif/DP Dibayar pada Stage 2
                        $perpanjangan->status = 'Menunggu Validasi';
                        $perpanjangan->save();

                        $isLunas = $perpanjangan->tipe_pembayaran === 'Lunas';
                        $jenisPembayaran = $isLunas ? 'Pelunasan Perpanjangan' : 'DP Perpanjangan';
                        $nominalPembayaran = $isLunas ? (float) $perpanjangan->nominal_total : (float) $perpanjangan->nominal_dp;
                        $labelTipe = $isLunas ? 'Lunas' : 'DP';

                        // Buat atau update transaksi Pembayaran
                        $pembayaran = Pembayaran::where('perpanjangan_id', $perpanjangan->id)
                            ->where('jenis_pembayaran', $jenisPembayaran)
                            ->where(function ($query) use ($transactionId) {
                                $query->where('midtrans_transaction_id', $transactionId)
                                    ->orWhereNull('midtrans_transaction_id');
                            })
                            ->first();

                        if (! $pembayaran) {
                            $kodePembayaran = 'PAY-'.date('Ymd').'-'.strtoupper(Str::random(5));
                            $pembayaran = new Pembayaran;
                            $pembayaran->kode_pembayaran = $kodePembayaran;
                            $pembayaran->user_id = $perpanjangan->user_id;
                            $pembayaran->perpanjangan_id = $perpanjangan->id;
                            $pembayaran->jenis_pembayaran = $jenisPembayaran;
                            $pembayaran->nominal = $nominalPembayaran;
                            $pembayaran->metode_pembayaran = 'QRIS';
                        }

                        $pembayaran->midtrans_transaction_id = $transactionId;
                        $pembayaran->midtrans_payment_type = $paymentType;
                        $pembayaran->tanggal_bayar = $paidAt->toDateString();
                        $pembayaran->status = 'Menunggu Validasi';
                        $pembayaran->catatan_penghuni = 'Pembayaran '.$labelTipe.' QRIS via Midtrans Sandbox (Order ID: '.$orderId.')';
                        $pembayaran->save();

                        $nomorKamar = $perpanjangan->sewa->kamar->nomor_kamar ?? '-';

                        RiwayatAktivitas::catat(
                            $perpanjangan->user_id,
                            'Pembayaran '.$labelTipe.' QRIS Perpanjangan Diterima',
                            'Pembayaran '.$labelTipe.' QRIS perpanjangan kamar '.$nomorKamar.' ('.$perpanjangan->durasi_bulan.' Bulan) sebesar Rp '.number_format($nominalPembayaran, 0, ',', '.').' berhasil dan menunggu validasi Pemilik Kost.',
                            'success'
                        );

                        $notification = new BusinessNotification(
                            'perpanjangan',
                            'menunggu_validasi',
                            'Pembayaran '.$labelTipe.' QRIS Perpanjangan Diterima',
                            'Pembayaran '.$labelTipe.' QRIS perpanjangan kamar '.$nomorKamar.' telah diterima Midtrans dan menunggu validasi Anda.',
                            'perpanjangan:'.$perpanjangan->id.':menunggu_validasi',
                            ['entity_type' => 'perpanjangan', 'entity_id' => $perpanjangan->id, 'actor_id' => $perpanjangan->user_id]
                        );

                        $this->notificationService->sendToRoleAfterCommit('pemilik-kost', $notification, $perpanjangan->user_id);
                        $this->notificationService->sendToRoleAfterCommit('super-admin', $notification, $perpanjangan->user_id);
                    }
                } elseif (str_starts_with($orderId, 'BILL-')) {
                    // ==========================================
                    // ALUR TAGIHAN BULANAN QRIS
                    // ==========================================
                    // Format order_id: BILL-{tagihan_id}-{timestamp}
                    $orderParts = explode('-', $orderId);
                    $tagihanId = $orderParts[1] ?? null;

                    $tagihan = Tagihan::with(['kamar', 'user'])
                        ->where('id', $tagihanId)
                        ->lockForUpdate()
                        ->first();

                    if (! $tagihan) {
                        Log::warning('Midtrans Webhook: Tagihan not found for order_id', [
                            'order_id' => $orderId,
                            'tagihan_id' => $tagihanId,
                        ]);
                        $response = response()->json([
                            'status' => 'error',
                            'message' => 'Tagihan not found',
                        ], 404);

                        return;
                    }

                    // Pengujian Idempotensi: jika Pembayaran untuk transaksi ini sudah ada
                    $existingPembayaran = Pembayaran::where('tagihan_id', $tagihan->id)
                        ->where(function ($query) use ($transactionId, $orderId) {
                            $query->where('midtrans_transaction_id', $transactionId)
                                ->orWhere('catatan_penghuni', 'like', '%'.$orderId.'%');
                        })
                        ->first();

                    if ($existingPembayaran && in_array($existingPembayaran->status, ['Menunggu Validasi', 'Lunas'])) {
                        // Pencegahan downgrade status: jika sudah diproses, jangan diubah oleh notifikasi tertunda
                        if (! $isSuccessStatus) {
                            Log::info('Midtrans Webhook: Ignoring delayed non-success status for tagihan', [
                                'order_id' => $orderId,
                                'received_status' => $transactionStatus,
                                'pembayaran_status' => $existingPembayaran->status,
                            ]);
                            $response = response()->json([
                                'status' => 'success',
                                'message' => 'Ignored delayed webhook',
                            ], 200);

                            return;
                        }

                        Log::info('Midtrans Webhook: Notification already processed (idempotent for tagihan)', [
                            'order_id' => $orderId,
                            'tagihan_id' => $tagihan->id,
                            'pembayaran_id' => $existingPembayaran->id,
                        ]);
                        $response = response()->json([
                            'status' => 'success',
                            'message' => 'Notification already processed (idempotent)',
                        ], 200);

                        return;
                    }

                    if ($isSuccessStatus) {
                        $paidAt = isset($payload['settlement_time'])
                            ? Carbon::parse($payload['settlement_time'])
                            : Carbon::now();

                        // PENTING: Tagihan TIDAK boleh otomatis menjadi Lunas pada webhook.
                        // Status Tagihan tetap tidak diubah ke Lunas (tetap status saat ini atau Belum Dibayar).
                        // Pemilik kost yang akan melakukan validasi.

                        // Buat atau update transaksi Pembayaran
                        $pembayaran = Pembayaran::where('tagihan_id', $tagihan->id)
                            ->where('jenis_pembayaran', 'Tagihan Bulanan')
                            ->where(function ($query) use ($transactionId) {
                                $query->where('midtrans_transaction_id', $transactionId)
                                    ->orWhereNull('midtrans_transaction_id');
                            })
                            ->first();

                        if (! $pembayaran) {
                            $kodePembayaran = 'PAY-'.date('Ymd').'-'.strtoupper(Str::random(5));
                            $pembayaran = new Pembayaran;
                            $pembayaran->kode_pembayaran = $kodePembayaran;
                            $pembayaran->user_id = $tagihan->user_id;
                            $pembayaran->tagihan_id = $tagihan->id;
                            $pembayaran->jenis_pembayaran = 'Tagihan Bulanan';
                            $pembayaran->nominal = $tagihan->total_bayar;
                            $pembayaran->metode_pembayaran = 'QRIS';
                        }

                        $pembayaran->midtrans_transaction_id = $transactionId;
                        $pembayaran->midtrans_payment_type = $paymentType;
                        $pembayaran->tanggal_bayar = $paidAt->toDateString();
                        $pembayaran->status = 'Menunggu Validasi';
                        $pembayaran->catatan_penghuni = 'Pembayaran Tagihan Bulanan QRIS via Midtrans Sandbox (Order ID: '.$orderId.')';
                        $pembayaran->save();

                        $nomorKamar = $tagihan->kamar->nomor_kamar ?? '-';

                        RiwayatAktivitas::catat(
                            $tagihan->user_id,
                            'Pembayaran Tagihan QRIS Diterima',
                            'Pembayaran Tagihan QRIS periode '.$tagihan->periode.' ('.$tagihan->nomor_tagihan.') sebesar Rp '.number_format($tagihan->total_bayar, 0, ',', '.').' berhasil dan menunggu validasi Pemilik Kost.',
                            'success'
                        );

                        $notification = new BusinessNotification(
                            'pembayaran',
                            'menunggu_validasi',
                            'Pembayaran Tagihan QRIS Diterima',
                            'Pembayaran Tagihan Bulanan QRIS periode '.$tagihan->periode.' ('.$tagihan->nomor_tagihan.') telah diterima Midtrans dan menunggu validasi Anda.',
                            'tagihan:'.$tagihan->id.':menunggu_validasi',
                            ['entity_type' => 'tagihan', 'entity_id' => $tagihan->id, 'actor_id' => $tagihan->user_id]
                        );

                        $this->notificationService->sendToRoleAfterCommit('pemilik-kost', $notification, $tagihan->user_id);
                        $this->notificationService->sendToRoleAfterCommit('super-admin', $notification, $tagihan->user_id);
                    }
                } else {
                    // ==========================================
                    // ALUR BOOKING AWAL QRIS (ASLI/TIDAK DIUBAH)
                    // ==========================================
                    // Gunakan lockForUpdate untuk menghindari race condition
                    $booking = Booking::where('midtrans_order_id', $orderId)->lockForUpdate()->first();

                    if (! $booking) {
                        Log::warning('Midtrans Webhook: Booking not found for order_id', [
                            'order_id' => $orderId,
                        ]);
                        $response = response()->json([
                            'status' => 'error',
                            'message' => 'Booking not found',
                        ], 404);

                        return;
                    }
// 3. Validate gross amount matches expected booking amount
                            $expectedAmount = $booking->tipe_pembayaran === 'Lunas' ? (float) $booking->total_harga : (float) $booking->nominal_dp;
                            $receivedAmount = (float) $grossAmount;
                            if (abs($expectedAmount - $receivedAmount) > 0.01) {
                                Log::warning('Midtrans Webhook: Gross amount mismatch', [
                                    'order_id' => $orderId,
                                    'expected' => $expectedAmount,
                                    'received' => $receivedAmount,
                                ]);
                                $response = response()->json([
                                    'status' => 'error',
                                    'message' => 'Gross amount does not match expected payment amount',
                                ], 400);
                                return;
                            }
                    // Pencegahan downgrade status: jika sudah sukses, jangan di-override oleh webhook pending/expire yang datang terlambat
                    if (in_array($booking->midtrans_status, ['settlement', 'capture'])) {
                        if (! $isSuccessStatus) {
                            Log::info('Midtrans Webhook: Ignoring delayed non-success status', [
                                'order_id' => $orderId,
                                'received_status' => $transactionStatus,
                                'current_status' => $booking->midtrans_status,
                            ]);
                            $response = response()->json([
                                'status' => 'success',
                                'message' => 'Ignored delayed webhook',
                            ], 200);

                            return;
                        }
                    }

                    // Pengujian Idempotensi
                    $existingPembayaran = Pembayaran::where('booking_id', $booking->id)
                        ->where(function ($q) use ($transactionId) {
                            $q->where('midtrans_transaction_id', $transactionId)
                                ->orWhere('status', 'Lunas');
                        })
                        ->first();

                    if ($isSuccessStatus && $existingPembayaran && $booking->status === 'Aktif') {
                        Log::info('Midtrans Webhook: Notification already processed (idempotent)', [
                            'order_id' => $orderId,
                            'booking_id' => $booking->id,
                            'booking_status' => $booking->status,
                        ]);
                        $response = response()->json([
                            'status' => 'success',
                            'message' => 'Notification already processed (idempotent)',
                        ], 200);

                        return;
                    }

                    if ($transactionStatus === 'pending') {
                        $booking->status = 'Menunggu Pembayaran';
                        $booking->save();

                    } elseif ($transactionStatus === 'settlement' || $transactionStatus === 'capture') {
                        // Successful payment: activate booking, mark payment as Lunas, allocate room, and create sewa
                        $booking->midtrans_status = $transactionStatus;
                        $booking->status = 'Aktif';
                        $booking->midtrans_paid_at = now();
                        $booking->save();

                        // Create or update payment record for booking
                        $pembayaran = Pembayaran::where('booking_id', $booking->id)
                            ->where(function ($q) use ($transactionId) {
                                $q->where('midtrans_transaction_id', $transactionId)
                                    ->orWhereNull('midtrans_transaction_id');
                            })
                            ->first();

                        if (! $pembayaran) {
                            $kodePembayaran = 'PAY-'.date('Ymd').'-'.strtoupper(Str::random(5));
                            $pembayaran = new Pembayaran;
                            $pembayaran->kode_pembayaran = $kodePembayaran;
                            $pembayaran->user_id = $booking->user_id;
                            $pembayaran->booking_id = $booking->id;
                            $pembayaran->jenis_pembayaran = 'Booking Awal';
                            $pembayaran->nominal = $booking->tipe_pembayaran === 'Lunas' ? (float) $booking->total_harga : (float) $booking->nominal_dp;
                            $pembayaran->metode_pembayaran = 'QRIS';
                        }
                        $pembayaran->midtrans_transaction_id = $transactionId;
                        $pembayaran->midtrans_payment_type = $paymentType;
                        $pembayaran->tanggal_bayar = now()->toDateString();
                        $pembayaran->status = 'Lunas';
                        $pembayaran->catatan_penghuni = 'Pembayaran QRIS Booking via Midtrans (Order ID: '.$orderId.')';
                        $pembayaran->save();

                        // Update room status to Terisi
                        $kamar = Kamar::lockForUpdate()->find($booking->kamar_id);
                        if ($kamar) {
                            $kamar->status = 'Terisi';
                            $kamar->save();
                        }

                        // Create active sewa record if not existing
                        $sewa = Sewa::where('booking_id', $booking->id)->first();
                        if (! $sewa) {
                            $tanggalMulai = Carbon::parse($booking->tanggal_mulai);
                            $durasiBulan = (int) $booking->durasi_bulan;
                            $tanggalSelesai = $tanggalMulai->copy()->addMonths($durasiBulan)->subDay();

                            $sewa = new Sewa;
                            $sewa->user_id = $booking->user_id;
                            $sewa->kamar_id = $booking->kamar_id;
                            $sewa->booking_id = $booking->id;
                            $sewa->status = 'Aktif';
                            $sewa->harga_per_bulan = $kamar ? (float) $kamar->harga : 0;
                            $sewa->tanggal_mulai = $tanggalMulai;
                            $sewa->tanggal_selesai = $tanggalSelesai;
                            $sewa->save();

                            $hargaPerBulan = $kamar ? (float) $kamar->harga : 0;
                            if ($booking->tipe_pembayaran === 'Lunas') {
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
                                        'total_bayar' => 0,
                                        'tanggal_jatuh_tempo' => $jatuhTempoBulan,
                                        'status' => 'Lunas',
                                    ]);
                                }
                            } else {
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
                        }

                        RiwayatAktivitas::catat(
                            $booking->user_id,
                            'Pembayaran QRIS Booking Diterima',
                            'Pembayaran QRIS booking '.$booking->kode_booking.' berhasil, kamar '.($kamar ? $kamar->nomor_kamar : '').' diaktifkan.',
                            'success'
                        );
                    } elseif ($transactionStatus === 'expire') {
                        $hasPayment = $booking->pembayarans()
                            ->whereIn('status', ['Menunggu Validasi', 'Lunas'])
                            ->exists();

                        if (! $hasPayment && ! in_array($booking->midtrans_status, ['settlement', 'capture'], true)) {
                            $booking->status = 'Kadaluarsa';
                            $booking->save();

                            $kamar = Kamar::lockForUpdate()->find($booking->kamar_id);
                            if ($kamar) {
                                $kamar->releaseIfAvailable($booking->id);
                            }

                            RiwayatAktivitas::catat(
                                $booking->user_id,
                                'Pembayaran QRIS Kedaluwarsa',
                                'Waktu pembayaran QRIS untuk booking '.$booking->kode_booking.' telah kedaluwarsa.',
                                'warning'
                            );
                        }

                    } elseif (in_array($transactionStatus, ['cancel', 'deny', 'failure'])) {
                        $hasPayment = $booking->pembayarans()
                            ->whereIn('status', ['Menunggu Validasi', 'Lunas'])
                            ->exists();

                        if (! $hasPayment && ! in_array($booking->midtrans_status, ['settlement', 'capture'], true)) {
                            $booking->status = 'Dibatalkan';
                            $booking->save();

                            $kamar = Kamar::lockForUpdate()->find($booking->kamar_id);
                            if ($kamar) {
                                $kamar->releaseIfAvailable($booking->id);
                            }

                            RiwayatAktivitas::catat(
                                $booking->user_id,
                                'Pembayaran QRIS Gagal',
                                'Pembayaran QRIS untuk booking '.$booking->kode_booking.' gagal ('.$transactionStatus.').',
                                'danger'
                            );
                        }
                    }
                }
            });

            if ($response) {
                return $response;
            }
        } catch (\Throwable $e) {
            Log::error('Midtrans Webhook: Error processing notification', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error while processing webhook',
            ], 500);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Notification processed successfully',
        ], 200);
    }
}
