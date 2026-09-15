@extends('layouts.dashboard')

@section('title', 'Evaluasi Perpanjangan Sewa')

@section('content')
<div class="mb-4">
    <a href="{{ route('pemilik.perpanjangan.index') }}" class="text-decoration-none small text-muted">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Perpanjangan
    </a>
    <h3 class="fw-bold text-secondary mt-1 mb-0">Evaluasi Pengajuan Perpanjangan Sewa (Model B)</h3>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-griya p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                <span class="badge bg-light text-primary border px-3 py-2 fw-semibold">
                    {{ $perpanjangan->sewa->kamar->nomor_kamar }} — Penghuni: {{ $perpanjangan->user->name }}
                </span>
                <span class="badge bg-primary px-3 py-2 fs-6">{{ $perpanjangan->status }}</span>
            </div>

            <div class="row g-3 small mb-4">
                <div class="col-sm-6">
                    <span class="text-muted d-block">Durasi Perpanjangan:</span>
                    <strong>{{ $perpanjangan->durasi_bulan }} Bulan</strong>
                </div>
                <div class="col-sm-6">
                    <span class="text-muted d-block">Periode Perpanjangan Baru:</span>
                    <strong>{{ $perpanjangan->tanggal_mulai_baru->format('d F Y') }} s/d {{ $perpanjangan->tanggal_selesai_baru->format('d F Y') }}</strong>
                </div>
                <div class="col-sm-6">
                    <span class="text-muted d-block">Total Kewajiban Sewa:</span>
                    <strong class="fs-5 text-secondary">Rp {{ number_format($perpanjangan->nominal_total, 0, ',', '.') }}</strong>
                </div>
                <div class="col-sm-6">
                    <span class="text-muted d-block">Kewajiban Uang Muka (DP):</span>
                    <strong class="fs-5 text-primary">Rp {{ number_format($perpanjangan->nominal_dp, 0, ',', '.') }} ({{ $perpanjangan->dp_persen }}%)</strong>
                </div>
            </div>

            @if($perpanjangan->catatan)
                <div class="p-3 bg-light rounded-3 mb-4 small">
                    <strong class="text-secondary d-block mb-1">Catatan dari Penghuni:</strong>
                    <span>{{ $perpanjangan->catatan }}</span>
                </div>
            @endif

            @if($perpanjangan->status === 'Menunggu Validasi')
                <div class="p-4 bg-light rounded-3 border">
                    <h5 class="fw-bold text-secondary mb-3">Persetujuan & Penentuan DP (Model B):</h5>
                    <form action="{{ route('pemilik.perpanjangan.approve', $perpanjangan->id) }}" method="POST" class="mb-3">
                        @csrf
                        @method('PATCH')
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-secondary">Persentase DP (%)</label>
                                <input type="number" name="dp_persen" id="dp_persen" class="form-control" value="{{ $perpanjangan->dp_persen }}" min="5" max="100" required oninput="document.getElementById('nominal_dp').value = Math.round({{ $perpanjangan->nominal_total }} * (this.value / 100));">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-semibold text-secondary">Nominal DP Wajib Bayar (Rp)</label>
                                <input type="number" name="nominal_dp" id="nominal_dp" class="form-control" value="{{ $perpanjangan->nominal_dp }}" min="1000" max="{{ $perpanjangan->nominal_total }}" required>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-success px-4" onclick="return confirm('Setujui pengajuan perpanjangan dengan nominal DP tersebut?')">
                            <i class="bi bi-check2-circle me-1"></i> Setujui Pengajuan & Tagihkan DP
                        </button>
                    </form>

                    <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="collapse" data-bs-target="#rejectPerpanjangan">
                        Tolak Pengajuan
                    </button>

                    <div class="collapse mt-2" id="rejectPerpanjangan">
                        <form action="{{ route('pemilik.perpanjangan.reject', $perpanjangan->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <textarea name="alasan_penolakan" rows="2" class="form-control mb-2" placeholder="Alasan penolakan..." required></textarea>
                            <button type="submit" class="btn btn-danger btn-sm">Konfirmasi Tolak</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-griya p-4">
            <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-credit-card text-primary me-2"></i>Status Pembayaran DP</h5>
            @if($perpanjangan->dpPembayaran)
                <div class="p-3 bg-light rounded-3 border small">
                    <div class="d-flex justify-content-between mb-1">
                        <strong>{{ $perpanjangan->dpPembayaran->kode_pembayaran }}</strong>
                        <span class="badge bg-{{ $perpanjangan->dpPembayaran->status === 'Lunas' ? 'success' : 'warning' }}">
                            {{ $perpanjangan->dpPembayaran->status }}
                        </span>
                    </div>
                    <div class="text-muted">Nominal: Rp {{ number_format($perpanjangan->dpPembayaran->nominal, 0, ',', '.') }}</div>
                    <a href="{{ route('pemilik.pembayaran.show', $perpanjangan->dpPembayaran->id) }}" class="btn btn-sm btn-outline-griya w-100 mt-2">
                        Periksa Bukti DP
                    </a>
                </div>
            @else
                <p class="text-muted small mb-0">Penghuni belum membayar / mengunggah bukti pembayaran DP.</p>
            @endif
        </div>
    </div>
</div>
@endsection
