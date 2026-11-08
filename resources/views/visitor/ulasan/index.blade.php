@extends('layouts.app')

@section('title', 'Rating & Ulasan Kost Putri Griya Ayu — Testimoni Penghuni')
@section('meta_description', 'Baca seluruh ulasan, pengalaman, dan penilaian jujur dari mahasiswi & karyawati penghuni Kost Putri Griya Ayu, Sleman, Yogyakarta.')

@section('content')
<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-primary">Beranda</a></li>
            <li class="breadcrumb-item active" aria-current="page">Rating & Ulasan</li>
        </ol>
    </nav>

    <!-- Header Section -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4 gap-3">
        <div>
            <span class="badge-tagline mb-2">Testimoni & Kepuasan Penghuni</span>
            <h1 class="display-6 fw-bold text-secondary mb-1">Rating & Ulasan Kost</h1>
            <p class="text-muted small mb-0">Ulasan terverifikasi dan pengalaman nyata langsung dari mahasiswi & karyawati penghuni Kost Putri Griya Ayu</p>
        </div>
        <div>
            <a href="{{ route('home') }}" class="btn btn-outline-griya btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali ke Beranda
            </a>
        </div>
    </div>

    <!-- Summary Statistik Rating -->
    <div class="card-griya p-4 p-md-5 mb-4">
        <div class="row align-items-center g-4">
            <div class="col-lg-4 text-center text-lg-start border-lg-end pe-lg-4">
                <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Rata-rata Rating Kost</span>
                <div class="d-flex align-items-baseline justify-content-center justify-content-lg-start gap-2 mb-2">
                    <span class="display-3 fw-bold text-secondary">{{ number_format($avgRating ?? 0, 1) }}</span>
                    <span class="text-muted fs-4">/ 5.0</span>
                </div>
                <div class="text-warning fs-5 mb-2">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="bi bi-star{{ $i <= round($avgRating) ? '-fill' : '' }}"></i>
                    @endfor
                </div>
                <p class="text-muted small mb-0">
                    Berdasarkan <strong>{{ $totalReviews ?? 0 }} ulasan</strong> disetujui dari penghuni aktif
                </p>
            </div>
            <div class="col-lg-8 ps-lg-4">
                <div class="d-flex flex-column gap-2">
                    @foreach([5, 4, 3, 2, 1] as $star)
                        @php
                            $count = $starCounts[$star] ?? 0;
                            $pct = $totalReviews > 0 ? round(($count / $totalReviews) * 100) : 0;
                        @endphp
                        <div class="d-flex align-items-center gap-3">
                            <div class="text-secondary small fw-semibold" style="width: 75px;">
                                ⭐ {{ $star }} Bintang
                            </div>
                            <div class="progress flex-grow-1" style="height: 10px; background-color: #e9eef3;">
                                <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $pct }}%;" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                            <div class="text-muted small text-end" style="width: 80px;">
                                <span>{{ $count }} ulasan</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Rating Bintang -->
    <div class="card-griya p-3 mb-4">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-funnel text-primary fs-5"></i>
                <span class="fw-bold text-secondary small">Filter Bintang:</span>
            </div>
            <div class="d-flex flex-wrap gap-2" id="starFilterPills">
                <a href="{{ route('ulasan.index') }}" class="btn btn-sm {{ !$filterRating ? 'btn-primary-griya' : 'btn-outline-secondary' }} rounded-pill px-3 star-filter-pill" data-star="all">
                    Semua Rating ({{ $totalReviews }})
                </a>
                @foreach([5, 4, 3, 2, 1] as $star)
                    <a href="{{ route('ulasan.index', ['rating' => $star]) }}" class="btn btn-sm {{ (string)$filterRating === (string)$star ? 'btn-primary-griya' : 'btn-outline-secondary' }} rounded-pill px-3 star-filter-pill" data-star="{{ $star }}">
                        ⭐ {{ $star }} ({{ $starCounts[$star] ?? 0 }})
                    </a>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Daftar Ulasan -->
    <div class="row g-3" id="ulasanCardsContainer">
        @forelse($ratings as $rating)
            <div class="col-12 ulasan-card-item" data-skor="{{ $rating->skor }}">
                <div class="card-griya p-4">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-start gap-2 mb-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon bg-primary-subtle text-primary fw-bold" style="width: 44px; height: 44px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                                {{ strtoupper(substr($rating->user->name ?? 'P', 0, 1)) }}
                            </div>
                            <div>
                                <h6 class="fw-bold text-secondary mb-0">{{ $rating->user->name ?? 'Penghuni' }}</h6>
                                <div class="d-flex align-items-center gap-2 mt-1">
                                    <span class="badge bg-light text-primary border">
                                        <i class="bi bi-door-closed me-1"></i>Kamar {{ $rating->kamar->nomor_kamar ?? '-' }}
                                    </span>
                                    <span class="text-muted small">
                                        {{ $rating->created_at ? $rating->created_at->translatedFormat('d F Y') : '-' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="text-warning fs-6">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="bi bi-star{{ $i <= ($rating->skor ?? 0) ? '-fill' : '' }}"></i>
                            @endfor
                            <span class="fw-bold text-secondary ms-1 small">{{ $rating->skor }}/5</span>
                        </div>
                    </div>

                    <p class="text-secondary small leading-relaxed mb-0">
                        {{ $rating->komentar ?: 'Penghuni menyukai kenyamanan, kebersihan, dan layanan di Kost Putri Griya Ayu.' }}
                    </p>

                    @if($rating->balasan)
                        <div class="mt-3 p-3 bg-light rounded-3 border-start border-3 border-primary small">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="fw-semibold text-primary" style="font-size: 0.8rem;">
                                    <i class="bi bi-reply-fill me-1"></i>Respon Pemilik Kost:
                                </span>
                                @if($rating->dibalas_pada)
                                    <span class="text-muted" style="font-size: 0.75rem;">
                                        {{ $rating->dibalas_pada->translatedFormat('d M Y') }}
                                    </span>
                                @endif
                            </div>
                            <p class="text-muted small mb-0">{{ $rating->balasan }}</p>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="col-12" id="emptyUlasanState">
                <div class="card-griya p-5 text-center">
                    <i class="bi bi-chat-square-text text-muted display-4 mb-3 d-block"></i>
                    <h5 class="fw-bold text-secondary mb-2">Belum Ada Ulasan</h5>
                    <p class="text-muted small mb-3">
                        @if($filterRating)
                            Tidak ada ulasan dengan rating ⭐ {{ $filterRating }} bintang saat ini.
                        @else
                            Belum ada ulasan yang disetujui untuk ditampilkan secara publik.
                        @endif
                    </p>
                    <div>
                        <a href="{{ route('ulasan.index') }}" class="btn btn-outline-griya btn-sm">
                            Tampilkan Semua Rating
                        </a>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    @if($ratings->hasPages())
        <div class="d-flex justify-content-center mt-5">
            {{ $ratings->appends(request()->query())->links() }}
        </div>
    @endif
</div>
@endsection

