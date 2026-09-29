@extends('layouts.dashboard')

@section('title', 'Detail Perpanjangan Sewa')

@section('content')
<div class="mb-4">
    <a href="{{ route('penghuni.perpanjangan.index') }}" class="text-decoration-none small text-muted">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Riwayat Perpanjangan
    </a>
    <h3 class="fw-bold text-secondary mt-1 mb-0">Rincian Pengajuan Perpanjangan Sewa</h3>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-griya p-4 p-md-5">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                <div>
                    <h4 class="fw-bold text-secondary mb-1">{{ $perpanjangan->sewa->kamar->nomor_kamar }}</h4>
                    <span class="text-muted small">Durasi Pengajuan: {{ $perpanjangan->durasi_bulan }} Bulan</span>
                </div>
                <div>
                    @if($perpanjangan->status === 'DP Dibayar' || $perpanjangan->status === 'Aktif')
                        <span class="badge bg-success fs-6 px-3 py-2">Perpanjangan Aktif</span>
                    @elseif($perpanjangan->dpPembayaran && $perpanjangan->dpPembayaran->status === 'Menunggu Validasi')
                        <span class="badge bg-warning text-dark fs-6 px-3 py-2">Menunggu Validasi Pembayaran</span>
                    @elseif($perpanjangan->status === 'Menunggu Pembayaran DP')
                        <span class="badge bg-primary fs-6 px-3 py-2">Menunggu Pembayaran {{ $perpanjangan->tipe_pembayaran === 'Lunas' ? 'Lunas' : 'DP' }}</span>
                    @elseif($perpanjangan->status === 'Ditolak')
                        <span class="badge bg-danger fs-6 px-3 py-2">Ditolak</span>
                    @else
                        <span class="badge bg-warning text-dark fs-6 px-3 py-2">Menunggu Persetujuan</span>
                    @endif
                </div>
            </div>

            <div class="row g-3 small mb-4">
                <div class="col-sm-6">
                    <span class="text-muted d-block">Periode Baru:</span>
                    <strong>{{ $perpanjangan->tanggal_mulai_baru->format('d F Y') }} s/d {{ $perpanjangan->tanggal_selesai_baru->format('d F Y') }}</strong>
                </div>
                <div class="col-sm-6">
                    <span class="text-muted d-block">Total Biaya Keseluruhan:</span>
                    <strong>Rp {{ number_format($perpanjangan->nominal_total, 0, ',', '.') }}</strong>
                </div>
                <div class="col-sm-6">
                    <span class="text-muted d-block">Tipe Pembayaran:</span>
                    <strong>{{ $perpanjangan->tipe_pembayaran === 'Lunas' ? 'Lunas (Bayar Penuh)' : 'DP 30% (Uang Muka)' }}</strong>
                </div>
                @if($perpanjangan->tipe_pembayaran === 'DP')
                <div class="col-sm-6">
                    <span class="text-muted d-block">Kewajiban Uang Muka (DP):</span>
                    <strong class="fs-5 text-primary">Rp {{ number_format($perpanjangan->nominal_dp, 0, ',', '.') }}</strong>
                    <span class="text-muted">({{ $perpanjangan->dp_persen }}%)</span>
                </div>
                <div class="col-sm-6">
                    <span class="text-muted d-block">Sisa Tagihan Bulanan:</span>
                    <strong>Rp {{ number_format($perpanjangan->nominal_total - $perpanjangan->nominal_dp, 0, ',', '.') }}</strong>
                </div>
                @else
                <div class="col-sm-6">
                    <span class="text-muted d-block">Nominal Pembayaran Lunas:</span>
                    <strong class="fs-5 text-primary">Rp {{ number_format($perpanjangan->nominal_total, 0, ',', '.') }}</strong>
                </div>
                @endif
            </div>

            @if($perpanjangan->catatan)
                <div class="p-3 bg-light rounded-3 mb-4 small">
                    <strong class="text-secondary d-block mb-1">Catatan Anda:</strong>
                    <span class="text-muted">{{ $perpanjangan->catatan }}</span>
                </div>
            @endif

            @if($perpanjangan->alasan_penolakan)
                <div class="alert alert-danger small mb-4">
                    <strong class="d-block mb-1"><i class="bi bi-x-circle me-1"></i> Catatan Penolakan Pemilik:</strong>
                    {{ $perpanjangan->alasan_penolakan }}
                </div>
            @endif

            @if($perpanjangan->dpPembayaran && $perpanjangan->dpPembayaran->status === 'Menunggu Validasi')
                <div class="alert alert-warning mb-0 d-flex align-items-center gap-2">
                    <i class="bi bi-clock-history fs-4"></i>
                    <div>
                        <strong class="d-block">Pembayaran Berhasil Dikirim!</strong>
                        <span class="small">Pembayaran Anda sebesar <strong>Rp {{ number_format($perpanjangan->dpPembayaran->nominal, 0, ',', '.') }}</strong> telah diterima sistem dan sedang menunggu validasi oleh Pemilik Kost.</span>
                    </div>
                </div>
            @elseif($perpanjangan->status === 'Menunggu Pembayaran DP')
                @php
                    $nominalBayar = $perpanjangan->tipe_pembayaran === 'Lunas' ? $perpanjangan->nominal_total : $perpanjangan->nominal_dp;
                    $labelBayar = $perpanjangan->tipe_pembayaran === 'Lunas' ? 'pembayaran Lunas' : 'DP';
                @endphp
                <div class="alert alert-primary mb-0 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                    <div>
                        <strong class="d-block mb-1">Pengajuan Disetujui!</strong>
                        <span class="small">Silakan bayar {{ $labelBayar }} sebesar <strong>Rp {{ number_format($nominalBayar, 0, ',', '.') }}</strong> untuk mengaktifkan perpanjangan.</span>
                    </div>
                    <a href="{{ route('penghuni.perpanjangan.bayar', $perpanjangan->id) }}" class="btn btn-primary-griya btn-sm flex-shrink-0">
                        <i class="bi bi-qr-code-scan me-1"></i> Bayar QRIS
                    </a>
                </div>
            @endif
        </div>
    </div>

    <!-- Status DP -->
    <div class="col-lg-4">
        <div class="card-griya p-4">
            <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-wallet2 text-primary me-2"></i>Status Pembayaran {{ $perpanjangan->tipe_pembayaran === 'Lunas' ? 'Lunas' : 'DP' }}</h5>
            @if($perpanjangan->dpPembayaran)
                <div class="p-3 bg-light rounded-3 border small">
                    <div class="d-flex justify-content-between mb-1">
                        <strong>{{ $perpanjangan->dpPembayaran->kode_pembayaran }}</strong>
                        <span class="badge bg-{{ $perpanjangan->dpPembayaran->status === 'Lunas' ? 'success' : 'warning' }}">
                            {{ $perpanjangan->dpPembayaran->status }}
                        </span>
                    </div>
                    <div class="text-muted">Nominal: Rp {{ number_format($perpanjangan->dpPembayaran->nominal, 0, ',', '.') }}</div>
                    <div class="text-muted">{{ $perpanjangan->dpPembayaran->tanggal_bayar ? $perpanjangan->dpPembayaran->tanggal_bayar->format('d M Y') : '' }}</div>
                </div>
            @else
                <p class="text-muted small mb-0">Belum ada pembayaran yang tercatat.</p>
            @endif
        </div>
    </div>
</div>
@endsection
