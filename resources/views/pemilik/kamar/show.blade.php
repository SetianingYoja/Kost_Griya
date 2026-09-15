@extends('layouts.dashboard')

@section('title', 'Detail Kamar ' . $kamar->nomor_kamar)

@section('content')
<div class="mb-4">
    <a href="{{ route('pemilik.kamar.index') }}" class="text-decoration-none small text-muted">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Kamar
    </a>
    <h3 class="fw-bold text-secondary mt-1 mb-0">Rincian {{ $kamar->nomor_kamar }}</h3>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-griya p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                <h4 class="fw-bold text-secondary mb-0">{{ $kamar->nomor_kamar }}</h4>
                <span class="badge bg-{{ $kamar->status === 'Tersedia' ? 'success' : ($kamar->status === 'Terisi' ? 'danger' : 'secondary') }} px-3 py-2 fs-6">
                    {{ $kamar->status }}
                </span>
            </div>

            <div class="row g-3 small mb-4">
                <div class="col-sm-4">
                    <span class="text-muted d-block">Tipe Kamar:</span>
                    <strong>{{ $kamar->tipeKamar->nama_tipe ?? '-' }}</strong>
                </div>
                <div class="col-sm-4">
                    <span class="text-muted d-block">Lantai:</span>
                    <strong>Lantai {{ $kamar->lantai }}</strong>
                </div>
                <div class="col-sm-4">
                    <span class="text-muted d-block">Tarif Sewa:</span>
                    <strong class="text-primary fs-5">Rp {{ number_format($kamar->harga, 0, ',', '.') }}</strong> / bln
                </div>
            </div>

            <div class="mb-3">
                <h6 class="fw-bold text-secondary mb-1">Fasilitas Kamar:</h6>
                <p class="text-muted small">{{ $kamar->fasilitas ?: 'Belum ada data fasilitas tercatat.' }}</p>
            </div>

            <div class="mb-3">
                <h6 class="fw-bold text-secondary mb-1">Deskripsi:</h6>
                <p class="text-muted small leading-relaxed">{{ $kamar->deskripsi ?: 'Tidak ada catatan deskripsi khusus.' }}</p>
            </div>

            <div class="d-flex gap-2 pt-3 border-top">
                <a href="{{ route('pemilik.kamar.edit', $kamar->id) }}" class="btn btn-primary-griya btn-sm">
                    <i class="bi bi-pencil me-1"></i> Edit Data Kamar
                </a>
            </div>
        </div>

        <!-- Penyewa Aktif -->
        <div class="card-griya p-4">
            <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-person-badge text-primary me-2"></i>Penyewa Saat Ini</h5>
            @if($kamar->currentSewa)
                <div class="p-3 bg-light rounded-3 border d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold text-secondary mb-1">{{ $kamar->currentSewa->user->name }}</h6>
                        <span class="small text-muted d-block"><i class="bi bi-whatsapp text-success me-1"></i> {{ $kamar->currentSewa->user->phone }}</span>
                        <span class="small text-muted"><i class="bi bi-calendar me-1"></i> {{ $kamar->currentSewa->tanggal_mulai->format('d M Y') }} s/d {{ $kamar->currentSewa->tanggal_selesai->format('d M Y') }}</span>
                    </div>
                    <a href="{{ route('pemilik.penghuni.show', $kamar->currentSewa->user_id) }}" class="btn btn-outline-griya btn-sm">
                        Profil Penyewa
                    </a>
                </div>
            @else
                <p class="text-muted small mb-0">Saat ini tidak ada penyewa aktif di kamar ini.</p>
            @endif
        </div>
    </div>

    <!-- Foto Kamar -->
    <div class="col-lg-4">
        <div class="card-griya p-4">
            <h5 class="fw-bold text-secondary mb-3">Foto Kamar</h5>
            @if($kamar->galleryImages->count())
                <div class="row g-2">
                    @foreach($kamar->galleryImages as $galleryImage)
                        <div class="col-6">
                            <img src="{{ $galleryImage->foto_url }}" alt="{{ $kamar->nomor_kamar }}" class="img-fluid rounded border" style="height: 110px; object-fit: cover; width: 100%;">
                        </div>
                    @endforeach
                </div>
            @else
                <img src="{{ $kamar->foto_url }}" alt="{{ $kamar->nomor_kamar }}" class="img-fluid rounded border mb-3">
            @endif
        </div>
    </div>
</div>
@endsection
