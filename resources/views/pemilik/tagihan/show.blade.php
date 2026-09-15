@extends('layouts.dashboard')

@section('title', 'Detail Tagihan ' . $tagihan->nomor_tagihan)

@section('content')
<div class="mb-4">
    <a href="{{ route('pemilik.tagihan.index') }}" class="text-decoration-none small text-muted">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Tagihan
    </a>
    <h3 class="fw-bold text-secondary mt-1 mb-0">Rincian Invoice: {{ $tagihan->nomor_tagihan }}</h3>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-griya p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                <span class="badge bg-light text-primary border px-3 py-2 fw-semibold">{{ $tagihan->periode }}</span>
                <span class="badge bg-{{ $tagihan->status === 'Lunas' ? 'success' : 'danger' }} px-3 py-2 fs-6">
                    {{ $tagihan->status }}
                </span>
            </div>

            <div class="row g-3 small mb-4">
                <div class="col-sm-6">
                    <span class="text-muted d-block">Nama Penghuni:</span>
                    <strong>{{ $tagihan->user->name }}</strong>
                    <div class="text-muted">{{ $tagihan->user->phone }}</div>
                </div>
                <div class="col-sm-6">
                    <span class="text-muted d-block">Kamar:</span>
                    <strong>{{ $tagihan->kamar->nomor_kamar ?? '-' }}</strong>
                </div>
                <div class="col-sm-6">
                    <span class="text-muted d-block">Tanggal Jatuh Tempo:</span>
                    <strong>{{ $tagihan->tanggal_jatuh_tempo->format('d F Y') }}</strong>
                </div>
                <div class="col-sm-6">
                    <span class="text-muted d-block">Total Wajib Bayar:</span>
                    <strong class="fs-4 text-primary">Rp {{ number_format($tagihan->total_bayar, 0, ',', '.') }}</strong>
                </div>
            </div>

            @if($tagihan->potongan_dp > 0)
                <div class="p-3 bg-light rounded-3 mb-3 small text-success">
                    <i class="bi bi-check-circle me-1"></i> Termasuk potongan DP perpanjangan (Model B) sebesar <strong>Rp {{ number_format($tagihan->potongan_dp, 0, ',', '.') }}</strong>.
                </div>
            @endif
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-griya p-4">
            <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-credit-card text-primary me-2"></i>Status Pembayaran</h5>
            @forelse($tagihan->pembayarans as $pembayaran)
                <div class="p-3 bg-light rounded-3 border mb-2 small">
                    <div class="d-flex justify-content-between mb-1">
                        <strong>{{ $pembayaran->kode_pembayaran }}</strong>
                        <span class="badge bg-{{ $pembayaran->status === 'Lunas' ? 'success' : 'warning' }}">{{ $pembayaran->status }}</span>
                    </div>
                    <div class="text-muted">Nominal: Rp {{ number_format($pembayaran->nominal, 0, ',', '.') }}</div>
                    <a href="{{ route('pemilik.pembayaran.show', $pembayaran->id) }}" class="btn btn-sm btn-outline-griya w-100 mt-2">
                        Periksa Bukti Bayar
                    </a>
                </div>
            @empty
                <p class="text-muted small mb-0">Penghuni belum mengunggah bukti pembayaran untuk invoice ini.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
