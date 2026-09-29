@extends('layouts.dashboard')

@section('title', 'Pembayaran QRIS Tagihan ' . $tagihan->nomor_tagihan)

@section('content')
<div class="mb-4">
    <a href="{{ route('penghuni.tagihan.show', $tagihan->id) }}" class="text-decoration-none small text-muted">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Detail Tagihan
    </a>
    <h3 class="fw-bold text-secondary mt-1 mb-0">Pembayaran Tagihan Sewa QRIS</h3>
</div>

<div class="row g-4 justify-content-center">
    <div class="col-lg-7 col-md-9">
        <div class="card-griya p-4 border-top border-4 border-primary shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                <div>
                    <span class="text-muted small d-block">Nomor Tagihan:</span>
                    <strong class="fs-5 text-dark">{{ $tagihan->nomor_tagihan }}</strong>
                </div>
                <span class="badge bg-warning text-dark px-3 py-2 fs-6 fw-semibold">
                    <i class="bi bi-clock-history me-1"></i> Menunggu Pembayaran
                </span>
            </div>

            <div class="bg-light p-3 rounded-3 mb-4 small">
                <div class="row g-2">
                    <div class="col-6">
                        <span class="text-muted d-block">Kamar:</span>
                        <strong>{{ $tagihan->kamar->nomor_kamar ?? '-' }} ({{ $tagihan->kamar->tipeKamar->nama_tipe ?? '' }})</strong>
                    </div>
                    <div class="col-6">
                        <span class="text-muted d-block">Periode:</span>
                        <strong>{{ $tagihan->periode }}</strong>
                    </div>
                    <div class="col-6">
                        <span class="text-muted d-block">Jatuh Tempo:</span>
                        <strong class="text-danger">{{ $tagihan->tanggal_jatuh_tempo ? $tagihan->tanggal_jatuh_tempo->format('d M Y') : '-' }}</strong>
                    </div>
                    <div class="col-6">
                        <span class="text-muted d-block">Metode Pembayaran:</span>
                        <strong class="text-primary"><i class="bi bi-qr-code-scan me-1"></i> QRIS Midtrans</strong>
                    </div>
                </div>
            </div>

            <!-- Detail Rincian Biaya -->
            <div class="p-3 border rounded-3 mb-4 bg-white">
                <h6 class="fw-bold text-secondary mb-3 pb-2 border-bottom">Rincian Tagihan</h6>
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Nominal Sewa:</span>
                    <span>Rp {{ number_format($tagihan->nominal, 0, ',', '.') }}</span>
                </div>
                @if($tagihan->potongan_dp > 0)
                    <div class="d-flex justify-content-between mb-2 small text-success">
                        <span>Potongan DP:</span>
                        <span>- Rp {{ number_format($tagihan->potongan_dp, 0, ',', '.') }}</span>
                    </div>
                @endif
                <div class="d-flex justify-content-between pt-2 border-top">
                    <strong class="text-secondary">Total Wajib Dibayar:</strong>
                    <strong class="fs-4 text-primary">Rp {{ number_format($tagihan->total_bayar, 0, ',', '.') }}</strong>
                </div>
            </div>

            <!-- Tombol Pemicu Snap Modal -->
            <div class="d-grid gap-2">
                <button type="button" id="pay-button" class="btn btn-primary-griya py-3 fs-6 fw-bold">
                    <i class="bi bi-qr-code-scan me-2"></i> Bayar QRIS Sekarang
                </button>
                <a href="{{ route('penghuni.tagihan.show', $tagihan->id) }}" class="btn btn-outline-secondary py-2 small">
                    Batal
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
    const snapToken = @json($snapToken);

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
                    window.location.href = "{{ route('penghuni.tagihan.show', $tagihan->id) }}";
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
