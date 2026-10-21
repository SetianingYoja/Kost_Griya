<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Booking extends Model
{
    use HasFactory;

    protected $table = 'bookings';

    protected $fillable = [
        'kode_booking',
        'user_id',
        'kamar_id',
        'tanggal_mulai',
        'durasi_bulan',
        'tipe_pembayaran',
        'total_harga',
        'catatan',
        'status',
        'alasan_penolakan',
        'batas_pembayaran',
        'nominal_dp',
        'midtrans_order_id',
        'midtrans_snap_token',
        'midtrans_status',
        'midtrans_paid_at',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'total_harga' => 'decimal:2',
        'nominal_dp' => 'decimal:2',
        'durasi_bulan' => 'integer',
        'batas_pembayaran' => 'datetime',
        'midtrans_paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function kamar(): BelongsTo
    {
        return $this->belongsTo(Kamar::class);
    }

    public function pembayarans(): HasMany
    {
        return $this->hasMany(Pembayaran::class);
    }

    public function latestPembayaran(): HasOne
    {
        return $this->hasOne(Pembayaran::class)->latestOfMany();
    }

    public function sewa(): HasOne
    {
        return $this->hasOne(Sewa::class);
    }

    /**
     * Pastikan semua relasi data booking berstatus Aktif terhubung dengan benar
     * (kamar Terisi, sewa Aktif terbuat, tagihan dibuat, dan pembayaran tercatat).
     */
    public static function ensureActiveBookingIntegrity(?self $specificBooking = null): void
    {
        $bookings = $specificBooking
            ? collect([$specificBooking])
            : static::where('status', 'Aktif')
                ->whereIn('midtrans_status', ['settlement', 'capture'])
                ->whereNotNull('midtrans_order_id')
                ->where(function ($query) {
                    $query->doesntHave('sewa')
                        ->orWhereDoesntHave('pembayarans')
                        ->orWhereHas('kamar', function ($kq) {
                            $kq->where('status', '!=', 'Terisi');
                        });
                })
                ->with(['kamar', 'sewa', 'pembayarans'])
                ->get();

        foreach ($bookings as $booking) {
            if ($booking->status !== 'Aktif') {
                continue;
            }

            // Hanya memproses booking yang memiliki bukti settlement Midtrans yang sah
            if (! in_array($booking->midtrans_status, ['settlement', 'capture'], true) || empty($booking->midtrans_order_id)) {
                continue;
            }

            // Jangan memproses jika pembayaran berstatus Ditolak
            if ($booking->pembayarans()->where('status', 'Ditolak')->exists()) {
                continue;
            }

            // 1. Pastikan kamar Terisi
            if ($booking->kamar && $booking->kamar->status !== 'Terisi') {
                $booking->kamar->update(['status' => 'Terisi']);
            }

            // 2. Pastikan Sewa terbentuk aman dari duplikasi
            $tanggalMulai = Carbon::parse($booking->tanggal_mulai);
            $durasiBulan = (int) $booking->durasi_bulan;
            $tanggalSelesai = $tanggalMulai->copy()->addMonths($durasiBulan)->subDay();

            $sewa = Sewa::firstOrCreate(
                ['booking_id' => $booking->id],
                [
                    'user_id' => $booking->user_id,
                    'kamar_id' => $booking->kamar_id,
                    'status' => 'Aktif',
                    'harga_per_bulan' => $booking->kamar ? (float) $booking->kamar->harga : 0,
                    'tanggal_mulai' => $tanggalMulai,
                    'tanggal_selesai' => $tanggalSelesai,
                ]
            );

            // Buat tagihan jika belum ada
            if (Tagihan::where('sewa_id', $sewa->id)->count() === 0) {
                $hargaPerBulan = $booking->kamar ? (float) $booking->kamar->harga : 0;
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

            // 3. Pastikan Pembayaran tercatat HANYA jika belum ada record pembayaran sama sekali
            if (! $booking->pembayarans()->exists()) {
                $nominal = $booking->tipe_pembayaran === 'Lunas'
                    ? (float) $booking->total_harga
                    : (float) ($booking->nominal_dp ?? ($booking->kamar ? $booking->kamar->harga * 0.3 : 0));

                Pembayaran::firstOrCreate(
                    ['booking_id' => $booking->id],
                    [
                        'kode_pembayaran' => 'PAY-'.date('Ymd').'-'.strtoupper(Str::random(5)),
                        'user_id' => $booking->user_id,
                        'jenis_pembayaran' => 'Booking Awal',
                        'nominal' => $nominal,
                        'metode_pembayaran' => 'QRIS',
                        'midtrans_transaction_id' => 'TRX-'.$booking->kode_booking,
                        'tanggal_bayar' => $booking->midtrans_paid_at ? $booking->midtrans_paid_at->toDateString() : ($booking->updated_at ? $booking->updated_at->toDateString() : now()->toDateString()),
                        'status' => 'Lunas',
                        'catatan_penghuni' => 'Pembayaran booking '.$booking->kode_booking,
                    ]
                );
            }
        }
    }
}
