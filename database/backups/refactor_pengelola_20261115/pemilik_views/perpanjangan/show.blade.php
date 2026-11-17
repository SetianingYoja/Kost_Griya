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
                @if($perpanjangan->status === 'DP Dibayar' || $perpanjangan->status === 'Aktif')
                    <span class="badge bg-success px-3 py-2 fs-6">Perpanjangan Aktif</span>
                @elseif($perpanjangan->dpPembayaran && $perpanjangan->dpPembayaran->status === 'Menunggu Validasi')
                    <span class="badge bg-warning text-dark px-3 py-2 fs-6">Menunggu Validasi Pembayaran</span>
                @elseif($perpanjangan->status === 'Menunggu Pembayaran DP')
                    <span class="badge bg-primary px-3 py-2 fs-6">Menunggu Bayar {{ ($perpanjangan->tipe_pembayaran ?? 'DP') === 'Lunas' ? 'Lunas' : 'DP' }}</span>
                @elseif($perpanjangan->status === 'Ditolak')
                    <span class="badge bg-danger px-3 py-2 fs-6">Ditolak</span>
                @else
                    <span class="badge bg-warning text-dark px-3 py-2 fs-6">Menunggu Persetujuan</span>
                @endif
            </div>

            <div class="row g-3 small mb-4">
                <div class="col-sm-6">
                    <span class="text-muted d-block">Durasi Perpanjangan:</span>
                    <strong>{{ $perpanjangan->durasi_bulan }} Bulan</strong>
                </div>
                <div class="col-sm-6">
                    <span class="text-muted d-block">Tipe Pembayaran:</span>
                    <span class="badge bg-{{ ($perpanjangan->tipe_pembayaran ?? 'DP') === 'Lunas' ? 'success' : 'info' }}">
                        {{ ($perpanjangan->tipe_pembayaran ?? 'DP') === 'Lunas' ? 'LUNAS (Bayar Penuh)' : 'DP (Uang Muka 30%)' }}
                    </span>
                </div>
                <div class="col-sm-6">
                    <span class="text-muted d-block">Periode Perpanjangan Baru:</span>
                    <strong>{{ $perpanjangan->tanggal_mulai_baru->format('d F Y') }} s/d {{ $perpanjangan->tanggal_selesai_baru->format('d F Y') }}</strong>
                </div>
                <div class="col-sm-6">
                    <span class="text-muted d-block">Total Kewajiban Sewa:</span>
                    <strong class="fs-5 text-secondary">Rp {{ number_format($perpanjangan->nominal_total, 0, ',', '.') }}</strong>
                </div>
                @if(($perpanjangan->tipe_pembayaran ?? 'DP') === 'Lunas')
                    <div class="col-sm-6">
                        <span class="text-muted d-block">Nominal Pelunasan:</span>
                        <strong class="fs-5 text-success">Rp {{ number_format($perpanjangan->nominal_total, 0, ',', '.') }}</strong>
                    </div>
                @else
                    <div class="col-sm-6">
                        <span class="text-muted d-block">Kewajiban Uang Muka (DP):</span>
                        <strong class="fs-5 text-primary">Rp {{ number_format($perpanjangan->nominal_dp, 0, ',', '.') }} ({{ $perpanjangan->dp_persen }}%)</strong>
                    </div>
                @endif
            </div>

            @if($perpanjangan->catatan)
                <div class="p-3 bg-light rounded-3 mb-4 small">
                    <strong class="text-secondary d-block mb-1">Catatan dari Penghuni:</strong>
                    <span>{{ $perpanjangan->catatan }}</span>
                </div>
            @endif

            {{-- Form Validasi Pembayaran jika pembayaran sudah masuk --}}
            @if($perpanjangan->dpPembayaran && $perpanjangan->dpPembayaran->status === 'Menunggu Validasi')
                <div class="p-4 bg-light rounded-3 border mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="fw-bold text-secondary mb-0">
                            <i class="bi bi-shield-check text-warning me-2"></i>Validasi Pembayaran {{ ($perpanjangan->tipe_pembayaran ?? 'DP') === 'Lunas' ? 'Pelunasan' : 'DP' }}
                        </h5>
                        <span class="badge bg-warning text-dark px-3 py-2">Menunggu Validasi Pembayaran</span>
                    </div>
                    <p class="small text-muted mb-3">
                        Pembayaran sebesar <strong>Rp {{ number_format($perpanjangan->dpPembayaran->nominal, 0, ',', '.') }}</strong> telah diterima via {{ $perpanjangan->dpPembayaran->metode_pembayaran }} (Kode: <code>{{ $perpanjangan->dpPembayaran->kode_pembayaran }}</code>). Silakan validasi untuk mengaktifkan masa sewa perpanjangan.
                    </p>
                    <form action="{{ route('pemilik.pembayaran.approve', $perpanjangan->dpPembayaran->id) }}" method="POST"
                          onsubmit="return confirm('Validasi pembayaran ini sebagai LUNAS? Sistem akan memperpanjang kontrak sewa dan mengaktifkan perpanjangan.')">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-success px-4">
                            <i class="bi bi-check2-circle me-1"></i> Validasi Pembayaran Sebagai LUNAS & Aktifkan Perpanjangan
                        </button>
                        <a href="{{ route('pemilik.pembayaran.show', $perpanjangan->dpPembayaran->id) }}" class="btn btn-outline-secondary px-3 ms-2">
                            Lihat Rincian Bukti
                        </a>
                    </form>
                </div>
            @endif

            {{-- Form Persetujuan Pengajuan jika baru diajukan dan belum ada pembayaran --}}
            @if($perpanjangan->status === 'Menunggu Validasi' && (!$perpanjangan->dpPembayaran || $perpanjangan->dpPembayaran->status !== 'Menunggu Validasi'))
                <div class="p-4 bg-light rounded-3 border">
                    <h5 class="fw-bold text-secondary mb-3">Persetujuan Perpanjangan:</h5>
                    <form action="{{ route('pemilik.perpanjangan.approve', $perpanjangan->id) }}" method="POST" class="mb-3">
                        @csrf
                        @method('PATCH')
                        
                        @php
                            $isLunas = ($perpanjangan->tipe_pembayaran ?? 'DP') === 'Lunas';
                            $hargaSewa1Bulan = $perpanjangan->sewa->harga_per_bulan ?? 0;
                            $nominalDpInfo = floor($hargaSewa1Bulan * 0.30);
                        @endphp
                        
                        <div class="alert alert-info py-2 small mb-3">
                            @if($isLunas)
                                <i class="bi bi-info-circle me-1"></i> Penghuni memilih pembayaran <strong>LUNAS</strong>. Nominal yang akan ditagihkan: <strong>Rp {{ number_format($perpanjangan->nominal_total, 0, ',', '.') }}</strong>.
                            @else
                                <i class="bi bi-info-circle me-1"></i> DP otomatis ditetapkan sebesar 30% dari harga 1 bulan sewa (<strong>Rp {{ number_format($nominalDpInfo, 0, ',', '.') }}</strong>).
                            @endif
                        </div>
                        <button type="submit" class="btn btn-success px-4" onclick="return confirm('{{ $isLunas ? 'Setujui pengajuan perpanjangan dengan pembayaran Lunas?' : 'Setujui pengajuan perpanjangan dengan nominal DP tersebut?' }}')">
                            <i class="bi bi-check2-circle me-1"></i> {{ $isLunas ? 'Setujui Pengajuan & Tagihkan Pelunasan' : 'Setujui Pengajuan & Tagihkan DP' }}
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
            <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-credit-card text-primary me-2"></i>Status Pembayaran {{ ($perpanjangan->tipe_pembayaran ?? 'DP') === 'Lunas' ? 'Lunas' : 'DP' }}</h5>
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
                        Periksa Bukti {{ ($perpanjangan->tipe_pembayaran ?? 'DP') === 'Lunas' ? 'Pelunasan' : 'DP' }}
                    </a>
                </div>
            @else
                <p class="text-muted small mb-0">Penghuni belum membayar / mengunggah bukti pembayaran {{ ($perpanjangan->tipe_pembayaran ?? 'DP') === 'Lunas' ? 'lunas' : 'DP' }}.</p>
            @endif
        </div>
    </div>
</div>
@endsection
