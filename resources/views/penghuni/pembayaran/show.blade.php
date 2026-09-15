@extends('layouts.dashboard')

@section('title', 'Detail Pembayaran ' . $pembayaran->kode_pembayaran)

@section('content')
<div class="mb-4">
    <a href="{{ route('penghuni.pembayaran.index') }}" class="text-decoration-none small text-muted">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Riwayat Pembayaran
    </a>
    <h3 class="fw-bold text-secondary mt-1 mb-0">Rincian Transaksi: {{ $pembayaran->kode_pembayaran }}</h3>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card-griya p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                <span class="badge bg-light text-primary border px-3 py-2 fw-semibold">{{ $pembayaran->jenis_pembayaran }}</span>
                @if($pembayaran->status === 'Lunas')
                    <span class="badge bg-success px-3 py-2 fs-6">Lunas</span>
                @elseif($pembayaran->status === 'Ditolak')
                    <span class="badge bg-danger px-3 py-2 fs-6">Ditolak</span>
                @else
                    <span class="badge bg-warning text-dark px-3 py-2 fs-6">Menunggu Validasi</span>
                @endif
            </div>

            <div class="row g-3 small mb-4">
                <div class="col-sm-6">
                    <span class="text-muted d-block">Nominal Transfer:</span>
                    <strong class="fs-4 text-primary">Rp {{ number_format($pembayaran->nominal, 0, ',', '.') }}</strong>
                </div>
                <div class="col-sm-6">
                    <span class="text-muted d-block">Tanggal Transfer:</span>
                    <strong>{{ $pembayaran->tanggal_bayar ? $pembayaran->tanggal_bayar->format('d F Y') : '-' }}</strong>
                </div>
                <div class="col-sm-6">
                    <span class="text-muted d-block">Bank Pengirim:</span>
                    <strong>{{ $pembayaran->bank_pengirim }}</strong>
                </div>
                <div class="col-sm-6">
                    <span class="text-muted d-block">Nama Rekening Pengirim:</span>
                    <strong>{{ $pembayaran->nama_pengirim }}</strong>
                </div>
            </div>

            @if($pembayaran->catatan_pemilik)
                <div class="alert alert-info small mb-3">
                    <strong class="d-block mb-1"><i class="bi bi-chat-quote me-1"></i> Tanggapan / Catatan dari Pemilik Kost:</strong>
                    {{ $pembayaran->catatan_pemilik }}
                </div>
            @endif

            @if($pembayaran->status === 'Ditolak')
                <div class="mt-3">
                    <a href="{{ route('penghuni.pembayaran.create', array_filter(['booking_id' => $pembayaran->booking_id, 'tagihan_id' => $pembayaran->tagihan_id, 'perpanjangan_id' => $pembayaran->perpanjangan_id])) }}" class="btn btn-primary-griya btn-sm">
                        <i class="bi bi-arrow-repeat me-1"></i> Unggah Ulang Bukti Pembayaran
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Foto Bukti Transfer -->
    <div class="col-lg-5">
        <div class="card-griya p-4">
            <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-file-earmark-image me-2 text-primary"></i>Lampiran Bukti Transfer</h5>
            @if($pembayaran->bukti_pembayaran)
                <div class="border rounded-3 overflow-hidden p-2 text-center bg-light">
                    <img src="{{ $pembayaran->bukti_url }}" alt="Bukti Transfer" class="img-fluid rounded" style="max-height: 400px;">
                </div>
                <div class="mt-3 text-center">
                    <a href="{{ $pembayaran->bukti_url }}" target="_blank" class="btn btn-sm btn-outline-griya">
                        <i class="bi bi-arrows-fullscreen me-1"></i> Buka Gambar Ukuran Asli
                    </a>
                </div>
            @else
                <p class="text-muted small">Tidak ada file bukti transfer terlampir.</p>
            @endif
        </div>
    </div>
</div>
@endsection
