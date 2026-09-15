@extends('layouts.dashboard')

@section('title', 'Penilaian & Rating Saya')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-secondary mb-1">Penilaian & Rating Layanan</h3>
        <p class="text-muted small mb-0">Ulasan kepuasan Anda terhadap hunian kost dan penanganan keluhan fasilitas</p>
    </div>
    <a href="{{ route('penghuni.rating.create') }}" class="btn btn-primary-griya btn-sm">
        <i class="bi bi-star me-1"></i> Beri Penilaian Kost
    </a>
</div>

<!-- Keluhan yang belum dinilai -->
@if($completedKeluhansWithoutRating->count() > 0)
    <div class="card-griya p-4 mb-4 border-warning">
        <h5 class="fw-bold text-secondary mb-2"><i class="bi bi-star text-warning me-2"></i>Keluhan Selesai yang Belum Anda Beri Penilaian</h5>
        <p class="small text-muted mb-3">Mohon berikan rating atas kecepatan dan kualitas penyelesaian keluhan Anda:</p>
        <div class="row g-2">
            @foreach($completedKeluhansWithoutRating as $item)
                <div class="col-md-6">
                    <div class="p-3 bg-light rounded-3 border d-flex justify-content-between align-items-center">
                        <div>
                            <strong class="d-block text-secondary small">{{ $item->judul }}</strong>
                            <small class="text-muted">Selesai: {{ $item->selesai_pada ? $item->selesai_pada->format('d M Y') : '' }}</small>
                        </div>
                        <a href="{{ route('penghuni.rating.create', ['keluhan_id' => $item->id]) }}" class="btn btn-sm btn-primary-griya">
                            Beri Skor
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif

<div class="card-griya p-4 mb-4">
    <form method="GET" action="{{ route('penghuni.rating.index') }}" class="row g-3 align-items-end">
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
            <a href="{{ route('penghuni.rating.index') }}" class="btn btn-outline-secondary btn-sm flex-fill">Reset</a>
        </div>
    </form>
</div>

<div class="card-griya p-4">
    <h5 class="fw-bold text-secondary mb-3">Riwayat Ulasan Anda</h5>
    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>Jenis Penilaian</th>
                    <th>Skor Bintang</th>
                    <th>Ulasan / Komentar</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($ratings as $rating)
                    <tr>
                        <td>
                            <span class="badge bg-light text-primary border">{{ $rating->jenis_rating }}</span>
                            @if($rating->keluhan)
                                <div class="text-muted small mt-1">Laporan: {{ $rating->keluhan->judul }}</div>
                            @endif
                        </td>
                        <td>
                            <div class="text-warning fs-6">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $rating->skor)
                                        <i class="bi bi-star-fill"></i>
                                    @else
                                        <i class="bi bi-star"></i>
                                    @endif
                                @endfor
                                <span class="text-secondary fw-bold ms-1">({{ $rating->skor }}/5)</span>
                            </div>
                        </td>
                        <td>
                            <span class="text-secondary">{{ $rating->komentar ?: '-' }}</span>
                        </td>
                        <td>{{ $rating->created_at->format('d M Y') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-4 text-muted">Belum ada penilaian yang diberikan.</td>
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
