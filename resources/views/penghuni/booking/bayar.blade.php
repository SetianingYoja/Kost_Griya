@extends('layouts.dashboard')

@section('title', ($booking->tipe_pembayaran === 'Lunas' ? 'Pembayaran Lunas' : 'Pembayaran DP') . ' Booking ' . $booking->kode_booking)

@section('content')
<div class="mb-4">
    <a href="{{ route('penghuni.booking.show', $booking->id) }}" class="text-decoration-none small text-muted">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Detail Booking
    </a>
    <h3 class="fw-bold text-secondary mt-1 mb-0">
        @if($booking->tipe_pembayaran === 'Lunas')
            Pembayaran Lunas Booking QRIS
        @else
            Pembayaran DP Booking QRIS
        @endif
    </h3>
</div>

<div class="row g-4 justify-content-center">
    <div class="col-lg-7 col-md-9">
        <div class="card-griya p-4 border-top border-4 border-primary shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                <div>
                    <span class="text-muted small d-block">Kode Booking:</span>
                    <strong class="fs-5 text-dark">{{ $booking->kode_booking }}</strong>
                </div>
                <span class="badge bg-warning text-dark px-3 py-2 fs-6 fw-semibold">
                    <i class="bi bi-clock-history me-1"></i> Menunggu Pembayaran
                </span>
            </div>

            <!-- Timer Countdown -->
            <div class="alert alert-warning d-flex align-items-center mb-4 py-2 px-3 rounded-3 small">
                <i class="bi bi-exclamation-triangle-fill me-2 fs-5 text-warning"></i>
                <div>
                    Selesaikan pembayaran QRIS sebelum batas waktu habis:
                    <strong id="countdown-timer" class="d-block fs-6 text-danger mt-1">--:--:--</strong>
                </div>
            </div>

            <div class="bg-light p-3 rounded-3 mb-4 small">
                <div class="row g-2">
                    <div class="col-6">
                        <span class="text-muted d-block">Kamar:</span>
                        <strong>{{ $booking->kamar->nomor_kamar }} ({{ $booking->kamar->tipeKamar->nama_tipe ?? '-' }})</strong>
                    </div>
                    <div class="col-6">
                        <span class="text-muted d-block">Tanggal Mulai:</span>
                        <strong>{{ $booking->tanggal_mulai->format('d F Y') }}</strong>
                    </div>
                    <div class="col-6">
                        <span class="text-muted d-block">Durasi Sewa:</span>
                        <strong>{{ $booking->durasi_bulan }} Bulan</strong>
                    </div>
                    <div class="col-6">
                        <span class="text-muted d-block">Metode Pembayaran:</span>
                        <strong class="text-primary"><i class="bi bi-qr-code-scan me-1"></i> QRIS Midtrans</strong>
                    </div>
                </div>
            </div>

            <!-- Detail Rincian Biaya -->
            <div class="p-3 border rounded-3 mb-4 bg-white">
                @if($booking->tipe_pembayaran === 'Lunas')
                    {{-- Tampilan LUNAS --}}
                    <h6 class="fw-bold text-secondary mb-3 pb-2 border-bottom">Rincian Pembayaran Lunas</h6>
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Tarif Sewa 1 Bulan:</span>
                        <span>Rp {{ number_format($booking->kamar->harga, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Durasi Sewa:</span>
                        <span class="fw-semibold">{{ $booking->durasi_bulan }} Bulan</span>
                    </div>
                    <div class="d-flex justify-content-between pt-2 border-top">
                        <strong class="text-secondary">Total Lunas yang Wajib Dibayar:</strong>
                        <strong class="fs-4 text-success">Rp {{ number_format($booking->total_harga, 0, ',', '.') }}</strong>
                    </div>
                    <div class="mt-2">
                        <span class="badge bg-success-subtle text-success border border-success-subtle small">
                            <i class="bi bi-check-circle me-1"></i> Setelah divalidasi, seluruh periode sewa dianggap lunas
                        </span>
                    </div>
                @else
                    {{-- Tampilan DP --}}
                    <h6 class="fw-bold text-secondary mb-3 pb-2 border-bottom">Rincian Pembayaran DP</h6>
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Tarif Sewa 1 Bulan:</span>
                        <span>Rp {{ number_format($booking->kamar->harga, 0, ',', '.') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 small">
                        <span class="text-muted">Ketentuan DP (30% dari 1 bulan):</span>
                        <span class="fw-semibold text-dark">30%</span>
                    </div>
                    <div class="d-flex justify-content-between pt-2 border-top">
                        <strong class="text-secondary">Nominal DP yang Wajib Dibayar:</strong>
                        <strong class="fs-4 text-primary">Rp {{ number_format($booking->nominal_dp, 0, ',', '.') }}</strong>
                    </div>
                @endif
            </div>

            <!-- Tombol Pemicu Snap Modal -->
            <div class="d-grid gap-2">
                <button type="button" id="pay-button" class="btn btn-primary-griya py-3 fs-6 fw-bold">
                    <i class="bi bi-qr-code-scan me-2"></i> Bayar QRIS Sekarang
                </button>
                <a href="{{ route('penghuni.booking.show', $booking->id) }}" class="btn btn-outline-secondary py-2 small">
                    Lihat Detail Pemesanan
                </a>
            </div>

            <div id="payment-status-message" class="mt-3"></div>
        </div>
    </div>
</div>

<!-- SDK Snap.js Midtrans Sandbox -->
<script 
    src="{{ config('midtrans.is_production', false) ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js' }}" 
    data-client-key="{{ config('midtrans.client_key') }}">
</script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const payButton = document.getElementById('pay-button');
    const snapToken = @json($booking->midtrans_snap_token);
    const expiryTime = new Date(@json($booking->batas_pembayaran ? $booking->batas_pembayaran->toIso8601String() : null)).getTime();
    const timerElement = document.getElementById('countdown-timer');

    // Countdown Timer 60 Menit
    function updateCountdown() {
        const now = new Date().getTime();
        const distance = expiryTime - now;

        if (distance <= 0) {
            timerElement.innerHTML = "WAKTU PEMBAYARAN KEDALUWARSA";
            payButton.disabled = true;
            payButton.classList.remove('btn-primary-griya');
            payButton.classList.add('btn-secondary');
            payButton.innerHTML = '<i class="bi bi-x-circle me-1"></i> Pembayaran Kedaluwarsa';
            return;
        }

        const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        timerElement.innerHTML = 
            (hours < 10 ? "0" + hours : hours) + ":" +
            (minutes < 10 ? "0" + minutes : minutes) + ":" +
            (seconds < 10 ? "0" + seconds : seconds);
    }

    if (expiryTime) {
        updateCountdown();
        setInterval(updateCountdown, 1000);
    }

    // Trigger Snap Pay Modal
    payButton.addEventListener('click', function () {
        if (!snapToken) {
            alert('Token pembayaran Midtrans tidak ditemukan. Silakan refresh halaman.');
            return;
        }

        window.snap.pay(snapToken, {
            onSuccess: function(result) {
                document.getElementById('payment-status-message').innerHTML = `
                    <div class="alert alert-success small">
                        <i class="bi bi-check-circle-fill me-1"></i> Pembayaran berhasil diproses oleh Midtrans! Sistem sedang memverifikasi transaksi.
                    </div>
                `;
                setTimeout(function() {
                    window.location.href = "{{ route('penghuni.booking.show', $booking->id) }}";
                }, 2000);
            },
            onPending: function(result) {
                document.getElementById('payment-status-message').innerHTML = `
                    <div class="alert alert-info small">
                        <i class="bi bi-info-circle-fill me-1"></i> Pembayaran QRIS diproses. Silakan selesaikan pembayaran pada aplikasi e-wallet Anda.
                    </div>
                `;
            },
            onError: function(result) {
                document.getElementById('payment-status-message').innerHTML = `
                    <div class="alert alert-danger small">
                        <i class="bi bi-exclamation-octagon-fill me-1"></i> Pembayaran gagal diproses oleh Midtrans. Silakan coba kembali.
                    </div>
                `;
            },
            onClose: function() {
                document.getElementById('payment-status-message').innerHTML = `
                    <div class="alert alert-warning small">
                        <i class="bi bi-info-circle me-1"></i> Tampilan pembayaran ditutup. Klik tombol "Bayar QRIS Sekarang" kembali jika ingin menyelesaikan transaksi.
                    </div>
                `;
            }
        });
    });
});
</script>
@endsection
