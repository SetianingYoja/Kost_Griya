@extends('layouts.dashboard')

@section('title', 'Rating & Ulasan Kepuasan Penghuni')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold text-secondary mb-1">Rating & Evaluasi Kepuasan</h3>
        <p class="text-muted small mb-0">Ulasan kepuasan fasilitas kost dan penilaian mutu penanganan keluhan</p>
    </div>
    <div class="btn-group">
        <a href="{{ route('pemilik.rating.index') }}" class="btn btn-sm {{ empty($jenis) ? 'btn-primary-griya' : 'btn-light border' }}">Semua</a>
        <a href="{{ route('pemilik.rating.index', ['jenis' => 'Kost']) }}" class="btn btn-sm {{ $jenis === 'Kost' ? 'btn-primary-griya' : 'btn-light border' }}">Fasilitas Kost</a>
        <a href="{{ route('pemilik.rating.index', ['jenis' => 'Penanganan Keluhan']) }}" class="btn btn-sm {{ $jenis === 'Penanganan Keluhan' ? 'btn-primary-griya' : 'btn-light border' }}">Penanganan Keluhan</a>
    </div>
</div>

<!-- Score Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div>
                <span class="small text-muted fw-medium d-block mb-1">Rata-rata Rating Kost</span>
                <h3 class="fw-bold text-warning mb-0"><i class="bi bi-star-fill me-1"></i>{{ number_format($avgKost, 1) }} / 5.0</h3>
                <small class="text-muted">Kepuasan fasilitas & suasana</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div>
                <span class="small text-muted fw-medium d-block mb-1">Rata-rata Penanganan Keluhan</span>
                <h3 class="fw-bold text-warning mb-0"><i class="bi bi-star-fill me-1"></i>{{ number_format($avgKeluhan, 1) }} / 5.0</h3>
                <small class="text-muted">Ketepatan & respon teknisi</small>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <div>
                <span class="small text-muted fw-medium d-block mb-1">Total Ulasan Masuk</span>
                <h3 class="fw-bold text-secondary mb-0">{{ $totalRating }}</h3>
                <small class="text-muted">Penilaian penghuni</small>
            </div>
        </div>
    </div>
</div>

<div class="card-griya p-4 mb-4">
    <form method="GET" action="{{ route('pemilik.rating.index') }}" class="row g-3 align-items-end">
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
            <a href="{{ route('pemilik.rating.index', request()->only('jenis')) }}" class="btn btn-outline-secondary btn-sm flex-fill">Reset</a>
        </div>
    </form>
</div>

<div class="card-griya p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>Penghuni</th>
                    <th>Kategori</th>
                    <th>Skor</th>
                    <th>Ulasan / Komentar</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ratings as $rating)
                    <tr>
                        <td><strong>{{ $rating->user->name }}</strong></td>
                        <td>
                            <span class="badge bg-light text-primary border">{{ $rating->jenis_rating }}</span>
                            @if($rating->keluhan)
                                <div class="text-muted small mt-1">Laporan: {{ $rating->keluhan->judul }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="text-warning">
                                @for($i=1;$i<=5;$i++)
                                    <i class="bi bi-star{{ $i <= $rating->skor ? '-fill' : '' }}"></i>
                                @endfor
                                <strong class="text-secondary ms-1">({{ $rating->skor }}/5)</strong>
                            </div>
                        </td>
                        <td>{{ $rating->komentar ?: '-' }}</td>
                        <td>{{ $rating->created_at->format('d/m/Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">Belum ada ulasan rating yang sesuai.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $ratings->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
