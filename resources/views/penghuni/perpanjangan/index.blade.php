@extends('layouts.dashboard')

@section('title', 'Perpanjangan Masa Sewa')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-secondary mb-1">Perpanjangan Masa Sewa (Model B)</h3>
        <p class="text-muted small mb-0">Ajukan perpanjangan kontrak sewa Anda dengan skema pembayaran DP</p>
    </div>
    @if($activeSewa)
        <a href="{{ route('penghuni.perpanjangan.create') }}" class="btn btn-primary-griya btn-sm">
            <i class="bi bi-plus-circle me-1"></i> Ajukan Perpanjangan Baru
        </a>
    @endif
</div>

<!-- Info Sewa Aktif Saat Ini -->
@if($activeSewa)
    <div class="card-griya p-4 mb-4 border-primary">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div>
                <span class="badge bg-primary px-3 py-1 rounded-pill mb-1">Masa Sewa Berjalan</span>
                <h4 class="fw-bold text-secondary mb-1">{{ $activeSewa->kamar->nomor_kamar }} ({{ $activeSewa->kamar->tipeKamar->nama_tipe ?? '' }})</h4>
                <p class="text-muted small mb-0">
                    Periode: <strong>{{ $activeSewa->tanggal_mulai->format('d M Y') }}</strong> s/d <strong class="text-danger">{{ $activeSewa->tanggal_selesai->format('d M Y') }}</strong> (Sisa: <strong>{{ $activeSewa->sisa_hari }} hari</strong>)
                </p>
            </div>
            <div>
                <a href="{{ route('penghuni.perpanjangan.create') }}" class="btn btn-primary-griya">
                    <i class="bi bi-arrow-repeat me-1"></i> Perpanjang Sewa Sekarang
                </a>
            </div>
        </div>
    </div>
@endif

<div class="card-griya p-4 mb-4">
    <form method="GET" action="{{ route('penghuni.perpanjangan.index') }}" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label small fw-semibold text-secondary">Dari Tanggal</label>
            <input type="date" name="from_date" class="form-control" value="{{ $fromDate ?? '' }}">
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-semibold text-secondary">Sampai Tanggal</label>
            <input type="date" name="to_date" class="form-control" value="{{ $toDate ?? '' }}">
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary-griya btn-sm flex-fill">Cari</button>
            <a href="{{ route('penghuni.perpanjangan.index') }}" class="btn btn-outline-secondary btn-sm flex-fill">Reset</a>
        </div>
    </form>
</div>

<div class="card-griya p-4">
    <h5 class="fw-bold text-secondary mb-3">Riwayat Pengajuan Perpanjangan</h5>
    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>Durasi</th>
                    <th>Periode Baru</th>
                    <th>Total Biaya</th>
                    <th>Kewajiban DP</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($perpanjangans as $perpanjangan)
                    <tr>
                        <td><strong>{{ $perpanjangan->durasi_bulan }} Bulan</strong></td>
                        <td>
                            {{ $perpanjangan->tanggal_mulai_baru->format('d M Y') }} s/d {{ $perpanjangan->tanggal_selesai_baru->format('d M Y') }}
                        </td>
                        <td>Rp {{ number_format($perpanjangan->nominal_total, 0, ',', '.') }}</td>
                        <td>
                            <strong class="text-primary">Rp {{ number_format($perpanjangan->nominal_dp, 0, ',', '.') }}</strong>
                            <small class="text-muted">({{ $perpanjangan->dp_persen }}%)</small>
                        </td>
                        <td>
                            @if($perpanjangan->status === 'Menunggu Validasi')
                                <span class="badge bg-warning text-dark">Menunggu Persetujuan</span>
                            @elseif($perpanjangan->status === 'Menunggu Pembayaran DP')
                                <span class="badge bg-primary">Menunggu Pembayaran DP</span>
                            @elseif($perpanjangan->status === 'DP Dibayar' || $perpanjangan->status === 'Aktif')
                                <span class="badge bg-success">Perpanjangan Aktif</span>
                            @elseif($perpanjangan->status === 'Ditolak')
                                <span class="badge bg-danger">Ditolak</span>
                            @else
                                <span class="badge bg-secondary">{{ $perpanjangan->status }}</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('penghuni.perpanjangan.show', $perpanjangan->id) }}" class="btn btn-sm btn-outline-griya">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                            @if($perpanjangan->status === 'Menunggu Pembayaran DP')
                                <a href="{{ route('penghuni.pembayaran.create', ['perpanjangan_id' => $perpanjangan->id]) }}" class="btn btn-sm btn-primary-griya ms-1">
                                    <i class="bi bi-upload"></i> Bayar DP
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Belum ada riwayat pengajuan perpanjangan sewa.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $perpanjangans->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
