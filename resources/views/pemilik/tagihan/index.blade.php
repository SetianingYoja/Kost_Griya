@extends('layouts.dashboard')

@section('title', 'Manajemen Tagihan Bulanan')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold text-secondary mb-1">Manajemen Tagihan Sewa</h3>
        <p class="text-muted small mb-0">Terbitkan tagihan bulanan dan pantau pembayaran invoice sewa kamar</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('pemilik.tagihan.create') }}" class="btn btn-primary-griya btn-sm">
            <i class="bi bi-plus-circle me-1"></i> Terbitkan Tagihan Baru
        </a>
    </div>
</div>

<div class="card-griya p-4 mb-4">
    <form method="GET" action="{{ route('pemilik.tagihan.index') }}" class="row g-3 align-items-end">
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
            <a href="{{ route('pemilik.tagihan.index', request()->only('status')) }}" class="btn btn-outline-secondary btn-sm flex-fill">Reset</a>
        </div>
    </form>
</div>

<div class="card-griya p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>No. Tagihan</th>
                    <th>Penghuni</th>
                    <th>Kamar</th>
                    <th>Periode</th>
                    <th>Jatuh Tempo</th>
                    <th>Total Wajib Bayar</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tagihans as $tgh)
                    <tr>
                        <td><strong>{{ $tgh->nomor_tagihan }}</strong></td>
                        <td>{{ $tgh->user->name ?? '-' }}</td>
                        <td><strong>{{ $tgh->kamar->nomor_kamar ?? '-' }}</strong></td>
                        <td>{{ $tgh->periode }}</td>
                        <td>{{ $tgh->tanggal_jatuh_tempo->format('d/m/Y') }}</td>
                        <td><strong class="text-primary">Rp {{ number_format($tgh->total_bayar, 0, ',', '.') }}</strong></td>
                        <td>
                            @if($tgh->status === 'Lunas')
                                <span class="badge bg-success">Lunas</span>
                            @elseif($tgh->status === 'Menunggu Validasi')
                                <span class="badge bg-warning text-dark">Validasi Bukti</span>
                            @elseif($tgh->status === 'Terlambat')
                                <span class="badge bg-danger">Terlambat</span>
                            @else
                                <span class="badge bg-danger">Belum Lunas</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('pemilik.tagihan.show', $tgh->id) }}" class="btn btn-sm btn-outline-griya py-1 px-2">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">Belum ada tagihan sewa.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $tagihans->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
