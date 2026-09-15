@extends('layouts.app')

@section('title', 'Pilihan Kamar Kost — Kost Putri Griya Ayu')

@section('content')
<section class="py-5 bg-light border-bottom">
    <div class="container">
        <div class="text-center max-w-700 mx-auto mb-4">
            <span class="badge-tagline mb-2">Katalog Kamar</span>
            <h1 class="display-6 fw-bold text-secondary">Pilihan Kamar Kost Putri Griya Ayu</h1>
            <p class="text-muted">Pilih kamar yang sesuai dengan kebutuhan kenyamanan Anda. Semua kamar dirawat bersih dan siap huni.</p>
        </div>

        <!-- Filter & Search Bar -->
        <div class="card-griya p-3 p-md-4 mb-4">
            <form action="{{ route('kamar.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-lg-4 col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Cari Kamar / Fasilitas</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-search"></i></span>
                        <input type="text" name="q" class="form-control bg-light border-start-0" placeholder="Nomor kamar, AC, kasur..." value="{{ request('q') }}">
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Tipe Kamar</label>
                    <select name="tipe" class="form-select bg-light">
                        <option value="">Semua Tipe</option>
                        @foreach($tipeKamars as $tipe)
                            <option value="{{ $tipe->id }}" {{ request('tipe') == $tipe->id ? 'selected' : '' }}>
                                {{ $tipe->nama_tipe }} (Rp {{ number_format($tipe->harga_bulanan, 0, ',', '.') }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-2 col-md-6">
                    <label class="form-label small fw-semibold text-secondary">Status Ketersediaan</label>
                    <select name="status" class="form-select bg-light">
                        <option value="">Semua Status</option>
                        <option value="Tersedia" {{ request('status') == 'Tersedia' ? 'selected' : '' }}>Tersedia</option>
                        <option value="Terisi" {{ request('status') == 'Terisi' ? 'selected' : '' }}>Terisi</option>
                        <option value="Tidak tersedia" {{ request('status') == 'Tidak tersedia' ? 'selected' : '' }}>Tidak Tersedia</option>
                    </select>
                </div>

                <div class="col-lg-3 col-md-6 d-flex gap-2">
                    <button type="submit" class="btn btn-primary-griya flex-grow-1">
                        <i class="bi bi-funnel me-1"></i> Terapkan Filter
                    </button>
                    @if(request()->anyFilled(['q', 'tipe', 'status', 'lantai']))
                        <a href="{{ route('kamar.index') }}" class="btn btn-light border text-muted">
                            <i class="bi bi-arrow-counterclockwise"></i> Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Room Grid -->
        <div class="row g-4">
            @forelse($kamars as $kamar)
                <div class="col-lg-4 col-md-6">
                    <div class="card-griya h-100 d-flex flex-column">
                        <div class="room-img-wrapper">
                            <img src="{{ $kamar->foto_url }}" alt="{{ $kamar->nomor_kamar }}">
                            <span class="room-badge-status status-{{ strtolower(str_replace(' ', '-', $kamar->status)) }}">
                                @if($kamar->status === 'Tersedia')
                                    <i class="bi bi-check-circle-fill me-1"></i> Tersedia
                                @elseif($kamar->status === 'Terisi')
                                    <i class="bi bi-x-circle-fill me-1"></i> Terisi
                                @else
                                    <i class="bi bi-slash-circle me-1"></i> Tidak Tersedia
                                @endif
                            </span>
                        </div>
                        <div class="p-4 d-flex flex-column flex-grow-1">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge bg-light text-primary border fw-semibold">{{ $kamar->tipeKamar->nama_tipe ?? 'Standar' }}</span>
                                <span class="small text-muted"><i class="bi bi-layers me-1"></i>Lantai {{ $kamar->lantai }}</span>
                            </div>
                            <h4 class="fw-bold text-secondary mb-2">{{ $kamar->nomor_kamar }}</h4>
                            <p class="text-muted small mb-3 flex-grow-1">
                                {{ Str::limit($kamar->fasilitas ?? $kamar->deskripsi, 90) }}
                            </p>
                            <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-auto">
                                <div>
                                    <span class="small text-muted d-block">Harga Sewa</span>
                                    <span class="price-tag">Rp {{ number_format($kamar->harga, 0, ',', '.') }}</span>
                                    <span class="small text-muted">/bln</span>
                                </div>
                                <a href="{{ route('kamar.detail', $kamar->id) }}" class="btn btn-primary-griya btn-sm px-3">
                                    Detail Kamar <i class="bi bi-chevron-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <div class="stat-icon mx-auto mb-3" style="background: rgba(100, 116, 139, 0.1); color: #64748B; width: 60px; height: 60px;">
                        <i class="bi bi-door-closed fs-3"></i>
                    </div>
                    <h5 class="fw-bold text-secondary">Kamar Tidak Ditemukan</h5>
                    <p class="text-muted small">Coba ubah kata kunci pencarian atau reset filter untuk melihat kamar lain.</p>
                    <a href="{{ route('kamar.index') }}" class="btn btn-outline-griya btn-sm">Tampilkan Semua Kamar</a>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        <div class="mt-5 d-flex justify-content-center">
            {{ $kamars->links('pagination::bootstrap-5') }}
        </div>
    </div>
</section>
@endsection
