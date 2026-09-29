@extends('layouts.dashboard')

@section('title', 'Pembayaran QRIS Perpanjangan')

@section('content')
<div class="mb-4">
    <a href="{{ route('penghuni.perpanjangan.show', $perpanjangan->id) }}" class="text-decoration-none small text-muted">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Detail Perpanjangan
    </a>
    <h3 class="fw-bold text-secondary mt-1 mb-0">Pembayaran {{ $perpanjangan->tipe_pembayaran === 'Lunas' ? 'Lunas' : 'DP' }} Perpanjangan QRIS</h3>
</div>

<div class="row g-4 justify-content-center">
    <div class="col-lg-7 col-md-9">
        <div class="card-griya p-4 border-top border-4 border-primary shadow-sm">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                <div>
                    <span class="text-muted small d-block">Durasi Perpanjangan:</span>
                    <strong class="fs-5 text-dark">{{ $perpanjangan->durasi_bulan }} Bulan</strong>
                </div>
                <span class="badge bg-warning text-dark px-3 py-2 fs-6 fw-semibold">
                    <i class="bi bi-clock-history me-1"></i> Menunggu Pembayaran
                </span>
            </div>

            <div class="bg-light p-3 rounded-3 mb-4 small">
                <div class="row g-2">
                    <div class="col-6">
                        <span class="text-muted d-block">Kamar:</span>
                        <strong>{{ $perpanjangan->sewa->kamar->nomor_kamar ?? '-' }}</strong>
                    </div>
                    <div class="col-6">
                        <span class="text-muted d-block">Periode Baru:</span>
                        <strong>{{ $perpanjangan->tanggal_mulai_baru->format('d M Y') }} - {{ $perpanjangan->tanggal_selesai_baru->format('d M Y') }}</strong>
                    </div>
                    <div class="col-6">
                        <span class="text-muted d-block">Harga Sewa per Bulan:</span>
                        <strong>Rp {{ number_format($perpanjangan->sewa->harga_per_bulan ?? 0, 0, ',', '.') }}</strong>
                    </div>
                    <div class="col-6">
                        <span class="text-muted d-block">Metode Pembayaran:</span>
                        <strong class="text-primary"><i class="bi bi-qr-code-scan me-1"></i> QRIS Midtrans</strong>
                    </div>
                </div>
            </div>

            <!-- Detail Rincian Biaya -->
            <div class="p-3 border rounded-3 mb-4 bg-white">
                <h6 class="fw-bold text-secondary mb-3 pb-2 border-bottom">Rincian Pembayaran {{ $perpanjangan->tipe_pembayaran === 'Lunas' ? 'Lunas' : 'DP' }}</h6>
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Total Tagihan ({{ $perpanjangan->durasi_bulan }} Bln):</span>
                    <span>Rp {{ number_format($perpanjangan->nominal_total, 0, ',', '.') }}</span>
                </div>
                <div class="d-flex justify-content-between pt-2 border-top">
                    <strong class="text-secondary">Nominal {{ $perpanjangan->tipe_pembayaran === 'Lunas' ? 'Lunas' : 'DP' }} yang Wajib Dibayar:</strong>
                    <strong class="fs-4 text-primary">Rp {{ number_format($perpanjangan->tipe_pembayaran === 'Lunas' ? $perpanjangan->nominal_total : $perpanjangan->nominal_dp, 0, ',', '.') }}</strong>
                </div>
            </div>

            <!-- Tombol Pemicu Snap Modal -->
            <div class="d-grid gap-2">
                <button type="button" id="pay-button" class="btn btn-primary-griya py-3 fs-6 fw-bold">
                    <i class="bi bi-qr-code-scan me-2"></i> Bayar QRIS Sekarang
                </button>
                <a href="{{ route('penghuni.perpanjangan.show', $perpanjangan->id) }}" class="btn btn-outline-secondary py-2 small">
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
                    window.location.href = "{{ route('penghuni.perpanjangan.show', $perpanjangan->id) }}";
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
