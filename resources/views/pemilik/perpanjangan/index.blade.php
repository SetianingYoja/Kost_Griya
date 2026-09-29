@extends('layouts.dashboard')

@section('title', 'Kelola Perpanjangan Sewa ')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold text-secondary mb-1">Pengajuan Perpanjangan Sewa</h3>
        <p class="text-muted small mb-0">Tinjau permohonan perpanjangan dan tetapkan nominal DP untuk penghuni</p>
    </div>
</div>

<div class="card-griya p-4 mb-4">
    <form method="GET" action="{{ route('pemilik.perpanjangan.index') }}" class="row g-3 align-items-end">
        @foreach(request()->except(['from_date','to_date','page']) as $key => $value)
            @if($value !== null && $value !== '')
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endif
        @endforeach
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
            <a href="{{ route('pemilik.perpanjangan.index', request()->only('status')) }}" class="btn btn-outline-secondary btn-sm flex-fill">Reset</a>
        </div>
    </form>
</div>

<div class="card-griya p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>Penghuni</th>
                    <th>Kamar</th>
                    <th>Durasi</th>
                    <th>Periode Baru</th>
                    <th>Total Biaya</th>
                    <th>Bayar Awal</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($perpanjangans as $perpanjangan)
                    <tr>
                        <td>
                            <strong>{{ $perpanjangan->user->name }}</strong>
                            <div class="text-muted small">{{ $perpanjangan->user->phone }}</div>
                        </td>
                        <td><strong>{{ $perpanjangan->sewa->kamar->nomor_kamar ?? '-' }}</strong></td>
                        <td>{{ $perpanjangan->durasi_bulan }} Bulan</td>
                        <td>{{ $perpanjangan->tanggal_mulai_baru->format('d/m/Y') }} s/d {{ $perpanjangan->tanggal_selesai_baru->format('d/m/Y') }}</td>
                        <td>Rp {{ number_format($perpanjangan->nominal_total, 0, ',', '.') }}</td>
                        <td>
                            @if(($perpanjangan->tipe_pembayaran ?? 'DP') === 'Lunas')
                                <span class="badge bg-success mb-1">LUNAS</span>
                                <div><strong class="text-success">Rp {{ number_format($perpanjangan->nominal_total, 0, ',', '.') }}</strong></div>
                            @else
                                <span class="badge bg-info text-dark mb-1">DP ({{ $perpanjangan->dp_persen }}%)</span>
                                <div><strong class="text-primary">Rp {{ number_format($perpanjangan->nominal_dp, 0, ',', '.') }}</strong></div>
                            @endif
                        </td>
                        <td>
                            @if($perpanjangan->status === 'DP Dibayar' || $perpanjangan->status === 'Aktif')
                                <span class="badge bg-success">Perpanjangan Aktif</span>
                            @elseif($perpanjangan->dpPembayaran && $perpanjangan->dpPembayaran->status === 'Menunggu Validasi')
                                <span class="badge bg-warning text-dark">Menunggu Validasi Bayar</span>
                            @elseif($perpanjangan->status === 'Menunggu Validasi')
                                <span class="badge bg-warning text-dark">Perlu Evaluasi</span>
                            @elseif($perpanjangan->status === 'Menunggu Pembayaran DP')
                                <span class="badge bg-primary">Menunggu Bayar {{ ($perpanjangan->tipe_pembayaran ?? 'DP') === 'Lunas' ? 'Lunas' : 'DP' }}</span>
                            @elseif($perpanjangan->status === 'Ditolak')
                                <span class="badge bg-danger">Ditolak</span>
                            @else
                                <span class="badge bg-secondary">{{ $perpanjangan->status }}</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('pemilik.perpanjangan.show', $perpanjangan->id) }}" class="btn btn-sm btn-outline-griya py-1 px-2">
                                <i class="bi bi-eye"></i> Evaluasi
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">Belum ada pengajuan perpanjangan sewa.</td>
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
