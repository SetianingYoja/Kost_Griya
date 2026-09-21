@extends('layouts.app')

@section('title', $kamar->nomor_kamar . ' — Detail Kamar Kost Griya Ayu')

@section('content')
<style>
    .gallery-lightbox-trigger {
        display: block;
        width: 100%;
        border: 0;
        padding: 0;
        background: transparent;
        cursor: pointer;
    }

    .gallery-lightbox-trigger img {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .gallery-lightbox-trigger:hover img {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.12);
    }

    .gallery-lightbox-modal .modal-content {
        background: rgba(0, 0, 0, 0.96);
        border: 0;
        border-radius: 1rem;
    }

    .gallery-lightbox-modal .modal-body {
        position: relative;
        padding: 0;
        min-height: 500px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #000;
    }

    .gallery-lightbox-viewport {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 100%;
        min-height: 500px;
        overflow: hidden;
        background: #000;
    }

    .gallery-lightbox-image {
        max-width: 100%;
        max-height: 78vh;
        object-fit: contain;
        transform-origin: center center;
        transition: transform 0.15s ease;
        user-select: none;
        -webkit-user-drag: none;
    }

    .gallery-lightbox-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        border: 0;
        width: 2.75rem;
        height: 2.75rem;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.18);
        color: #fff;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0;
        margin: 0;
        line-height: 1;
        flex-shrink: 0;
        box-shadow: 0 0.25rem 0.75rem rgba(0,0,0,0.18);
    }

    .gallery-lightbox-nav i {
        font-size: 1.3rem;
        line-height: 1;
        display: inline-block;
        width: auto;
        height: auto;
        flex-shrink: 0;
    }

    .gallery-lightbox-nav.prev {
        left: 1rem;
    }

    .gallery-lightbox-nav.next {
        right: 1rem;
    }

    .gallery-lightbox-nav:hover,
    .gallery-lightbox-nav:focus {
        background: rgba(255, 255, 255, 0.28);
        color: #fff;
    }

    .gallery-lightbox-toolbar {
        position: absolute;
        right: 1rem;
        bottom: 1rem;
        display: flex;
        gap: 0.5rem;
        z-index: 2;
    }

    .gallery-lightbox-toolbar .btn {
        width: 2.5rem;
        height: 2.5rem;
        border-radius: 50%;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        line-height: 1;
        border: 0;
    }

    .gallery-lightbox-caption {
        position: absolute;
        left: 1rem;
        bottom: 1rem;
        color: #fff;
        background: rgba(0, 0, 0, 0.34);
        border-radius: 999px;
        padding: 0.4rem 0.8rem;
        font-size: 0.85rem;
        backdrop-filter: blur(2px);
    }

    @media (max-width: 576px) {
        .gallery-lightbox-modal .modal-body,
        .gallery-lightbox-viewport {
            min-height: 360px;
        }

        .gallery-lightbox-nav {
            width: 2.5rem;
            height: 2.5rem;
        }
    }
</style>
<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none">Beranda</a></li>
            <li class="breadcrumb-item"><a href="{{ route('kamar.index') }}" class="text-decoration-none">Pilihan Kamar</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $kamar->nomor_kamar }}</li>
        </ol>
    </nav>

    <div class="row g-5">
        <!-- Main Details -->
        <div class="col-lg-8">
            <div class="card-griya overflow-hidden mb-4">
                <div class="room-img-wrapper" style="height: 420px;">
                    <button type="button" class="gallery-lightbox-trigger" data-index="0" data-image="{{ $kamar->foto_url }}" data-caption="{{ $kamar->nomor_kamar }}" aria-label="Lihat foto utama kamar">
                        <img src="{{ $kamar->foto_url }}" alt="{{ $kamar->nomor_kamar }}" class="w-100 h-100 object-fit-contain" style="display: block; object-fit: contain;">
                    </button>
                    <span class="room-badge-status status-{{ strtolower(str_replace(' ', '-', $kamar->status)) }}" style="top: 20px; right: 20px; font-size: 0.9rem; padding: 0.5rem 1rem;">
                        @if($kamar->status === 'Tersedia')
                            <i class="bi bi-check-circle-fill me-1"></i> Tersedia untuk Dipesan
                        @elseif($kamar->status === 'Terisi')
                            <i class="bi bi-x-circle-fill me-1"></i> Sedang Terisi
                        @else
                            <i class="bi bi-slash-circle me-1"></i> Sedang Tidak Tersedia
                        @endif
                    </span>
                </div>

                @if($kamar->galleryImages->count())
                    <div class="p-4 border-top">
                        <h4 class="fw-bold text-secondary mb-3">Galeri Foto Kamar</h4>
                        <div class="row g-3">
                            @foreach($kamar->galleryImages as $index => $galleryImage)
                                <div class="col-md-4 col-6">
                                    <button type="button" class="gallery-lightbox-trigger" data-index="{{ $index + 1 }}" data-image="{{ $galleryImage->foto_url }}" data-caption="{{ $kamar->nomor_kamar }}" aria-label="Lihat foto kamar {{ $kamar->nomor_kamar }}" style="width:100%;">
                                        <img src="{{ $galleryImage->foto_url }}" alt="{{ $kamar->nomor_kamar }}" class="img-fluid rounded border" style="height: 160px; object-fit: cover; width: 100%; display: block;">
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="p-4 p-md-5">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                        <div>
                            <span class="badge bg-light text-primary border px-3 py-2 fw-semibold mb-2 d-inline-block">
                                {{ $kamar->tipeKamar->nama_tipe ?? 'Tipe Kamar' }}
                            </span>
                            <h1 class="display-5 fw-bold text-secondary mb-0">{{ $kamar->nomor_kamar }}</h1>
                        </div>
                        <div class="text-md-end">
                            <span class="text-muted small d-block">Harga Sewa Bulanan</span>
                            <span class="fs-2 fw-bold text-primary">Rp {{ number_format($kamar->harga, 0, ',', '.') }}</span>
                            <span class="text-muted small">/bulan</span>
                        </div>
                    </div>

                    <div class="d-flex gap-4 py-3 my-3 border-top border-bottom text-secondary small">
                        <div><i class="bi bi-layers text-primary me-1 fs-5"></i> <strong>Lantai:</strong> {{ $kamar->lantai }}</div>
                        <div><i class="bi bi-people text-primary me-1 fs-5"></i> <strong>Kapasitas:</strong> 1 Orang (Putri)</div>
                        <div><i class="bi bi-shield-check text-primary me-1 fs-5"></i> <strong>Keamanan:</strong> CCTV 24 Jam</div>
                    </div>

                    <!-- Deskripsi -->
                    <div class="mb-4">
                        <h4 class="fw-bold text-secondary mb-2">Deskripsi Kamar</h4>
                        <p class="text-muted leading-relaxed">
                            {{ $kamar->deskripsi ?: 'Kamar bersih, nyaman, dan tenang di Kost Putri Griya Ayu dengan pencahayaan alami dan sirkulasi udara yang baik. Sangat cocok bagi Anda yang menginginkan kenyamanan belajar dan istirahat berkualitas.' }}
                        </p>
                    </div>

                    <!-- Fasilitas Lengkap -->
                    <div class="mb-4">
                        <h4 class="fw-bold text-secondary mb-3">Fasilitas Kamar</h4>
                        <div class="row g-2">
                            @if($kamar->fasilitas)
                                @foreach(explode(',', $kamar->fasilitas) as $fasilitas)
                                    <div class="col-md-6">
                                        <div class="p-3 bg-light rounded-3 d-flex align-items-center gap-2">
                                            <i class="bi bi-check2-circle text-primary fs-5"></i>
                                            <span class="fw-medium small text-secondary">{{ trim($fasilitas) }}</span>
                                        </div>
                                    </div>
                                @endforeach
                            @else
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3 d-flex align-items-center gap-2">
                                        <i class="bi bi-check2-circle text-primary fs-5"></i>
                                        <span class="fw-medium small text-secondary">Kasur Springbed Berkualitas</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3 d-flex align-items-center gap-2">
                                        <i class="bi bi-check2-circle text-primary fs-5"></i>
                                        <span class="fw-medium small text-secondary">Lemari Pakaian Pribadi</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3 d-flex align-items-center gap-2">
                                        <i class="bi bi-check2-circle text-primary fs-5"></i>
                                        <span class="fw-medium small text-secondary">Meja Kerja & Belajar</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-3 bg-light rounded-3 d-flex align-items-center gap-2">
                                        <i class="bi bi-check2-circle text-primary fs-5"></i>
                                        <span class="fw-medium small text-secondary">WiFi High-Speed Gratis</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Fasilitas Umum Bersama -->
                    <div class="mb-4">
                        <h4 class="fw-bold text-secondary mb-3">Fasilitas Umum Kost</h4>
                        <div class="row g-2">
                            <div class="col-md-4 col-6">
                                <div class="p-2 border rounded-3 text-center text-muted small">
                                    <i class="bi bi-camera-video text-primary d-block fs-4 mb-1"></i>
                                    CCTV 24 Jam
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="p-2 border rounded-3 text-center text-muted small">
                                    <i class="bi bi-cup-hot text-primary d-block fs-4 mb-1"></i>
                                    Dapur Bersama
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="p-2 border rounded-3 text-center text-muted small">
                                    <i class="bi bi-droplet text-primary d-block fs-4 mb-1"></i>
                                    Dispenser Air Minum
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="p-2 border rounded-3 text-center text-muted small">
                                    <i class="bi bi-snow text-primary d-block fs-4 mb-1"></i>
                                    Kulkas Bersama
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="p-2 border rounded-3 text-center text-muted small">
                                    <i class="bi bi-p-square text-primary d-block fs-4 mb-1"></i>
                                    Parkir Motor Aman
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="p-2 border rounded-3 text-center text-muted small">
                                    <i class="bi bi-sun text-primary d-block fs-4 mb-1"></i>
                                    Area Jemur Luas
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 bg-light rounded-4 border mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h5 class="fw-bold text-secondary mb-0"><i class="bi bi-star-fill text-warning me-2"></i>Rating Kamar</h5>
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-warning fw-bold">{{ number_format($avgKamarRating ?? 0, 1) }}/5</span>
                                <small class="text-muted">({{ $totalKamarReviews ?? 0 }} ulasan)</small>
                            </div>
                        </div>

                        @if($kamarRatings->count())
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <div class="text-warning">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="bi bi-star{{ $i <= round($avgKamarRating) ? '-fill' : '' }}"></i>
                                    @endfor
                                </div>
                                <span class="small text-secondary">Rata-rata dari penghuni aktif</span>
                            </div>

                            <div class="d-flex flex-column gap-3">
                                @foreach($kamarRatings->take(3) as $review)
                                    <div class="border rounded-3 bg-white p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <strong class="text-secondary small">{{ $review->user->name ?? 'Penghuni' }}</strong>
                                            <div class="text-warning small">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="bi bi-star{{ $i <= ($review->skor ?? 0) ? '-fill' : '' }}"></i>
                                                @endfor
                                            </div>
                                        </div>
                                        <p class="small text-muted mb-0">{{ $review->komentar ?: 'Penghuni merasa kamar ini nyaman dan layak dihuni.' }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <p class="text-muted small mb-0">Belum ada ulasan valid untuk kamar ini.</p>
                        @endif
                    </div>

                    <!-- Aturan Kost Ringkas -->
                    <div class="p-4 bg-light rounded-4 border">
                        <h5 class="fw-bold text-secondary mb-2"><i class="bi bi-journal-text text-primary me-2"></i>Tata Tertib Kost</h5>
                        <ul class="text-muted small mb-0 ps-3">
                            <li>Khusus Putri (Mahasiswi / Karyawati).</li>
                            <li>Tamu pria dilarang masuk ke dalam kamar. Menerima tamu di ruang tamu bersama hingga pukul 21.00 WIB.</li>
                            <li>Dilarang merokok di dalam kamar dan membawa hewan peliharaan.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sidebar Booking Action -->
        <div class="col-lg-4">
            <div class="card-griya p-4 sticky-top" style="top: 100px; z-index: 10;">
                <h4 class="fw-bold text-secondary mb-3">Pesan Kamar Ini</h4>
                <div class="p-3 bg-light rounded-3 mb-4">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted small">Tarif Sewa:</span>
                        <strong class="text-primary">Rp {{ number_format($kamar->harga, 0, ',', '.') }} / bln</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Status Kamar:</span>
                        @if($kamar->status === 'Tersedia')
                            <span class="badge bg-success">Tersedia</span>
                        @else
                            <span class="badge bg-danger">{{ $kamar->status }}</span>
                        @endif
                    </div>
                </div>

                @if($kamar->status === 'Tersedia')
                    @auth
                        @if(Auth::user()->isPenghuni())
                            <form action="{{ route('penghuni.booking.store') }}" method="POST">
                                @csrf
                                <input type="hidden" name="kamar_id" value="{{ $kamar->id }}">
                                
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-secondary">Tanggal Mulai Sewa</label>
                                    <input type="date" name="tanggal_mulai" class="form-control" min="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-secondary">Durasi Sewa Awal</label>
                                    <select name="durasi_bulan" class="form-select" required>
                                        <option value="1">1 Bulan (Rp {{ number_format($kamar->harga * 1, 0, ',', '.') }})</option>
                                        <option value="3">3 Bulan (Rp {{ number_format($kamar->harga * 3, 0, ',', '.') }})</option>
                                        <option value="6">6 Bulan (Rp {{ number_format($kamar->harga * 6, 0, ',', '.') }})</option>
                                        <option value="12">12 Bulan (1 Tahun) (Rp {{ number_format($kamar->harga * 12, 0, ',', '.') }})</option>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label small fw-semibold text-secondary">Catatan Tambahan (Opsional)</label>
                                    <textarea name="catatan" rows="2" class="form-control" placeholder="Contoh: Perkiraan waktu check-in sore..."></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary-griya w-100 py-3 fw-semibold">
                                    <i class="bi bi-calendar-check me-2"></i> Ajukan Booking Kamar
                                </button>
                                <p class="small text-muted text-center mt-2 mb-0">Permohonan booking akan divalidasi oleh Pemilik Kost.</p>
                            </form>
                        @else
                            <div class="alert alert-info small mb-0">
                                <i class="bi bi-info-circle me-1"></i> Anda masuk sebagai <strong>{{ Auth::user()->role->name }}</strong>. Fitur pemesanan kamar hanya dapat dilakukan melalui akun Penghuni.
                            </div>
                        @endif
                    @else
                        <div class="text-center py-2">
                            <p class="small text-muted mb-3">Silakan masuk atau daftar akun terlebih dahulu untuk melakukan pemesanan kamar ini.</p>
                            <a href="{{ route('login') }}" class="btn btn-primary-griya w-100 py-2 mb-2 fw-semibold">
                                <i class="bi bi-box-arrow-in-right me-1"></i> Masuk untuk Booking
                            </a>
                            <a href="{{ route('register') }}" class="btn btn-outline-griya w-100 py-2 fw-semibold">
                                <i class="bi bi-person-plus me-1"></i> Daftar Akun Baru
                            </a>
                        </div>
                    @endauth
                @else
                    <div class="alert alert-warning mb-3">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Kamar ini sedang <strong>{{ $kamar->status }}</strong> dan tidak dapat dibooking saat ini.
                    </div>
                    <a href="{{ route('kamar.index') }}" class="btn btn-outline-griya w-100">
                        <i class="bi bi-search me-1"></i> Cari Kamar Tersedia Lainnya
                    </a>
                @endif

                <hr class="my-4">

                <div class="text-center">
                    <p class="small text-muted mb-2">Ingin bertanya atau survei langsung?</p>
                    <a href="https://wa.me/6282242645466?text=Halo%20Kost%20Griya%20Ayu,%20saya%20tertarik%20dengan%20{{ urlencode($kamar->nomor_kamar) }}" target="_blank" class="btn btn-success w-100">
                        <i class="bi bi-whatsapp me-2"></i> Chat WhatsApp Pemilik
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade gallery-lightbox-modal" id="kamarGalleryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body">
                <button type="button" class="btn-close btn-close-white position-absolute top-0 end-0 m-3" data-bs-dismiss="modal" aria-label="Tutup"></button>

                <button type="button" class="gallery-lightbox-nav prev" id="galleryPrevBtn" aria-label="Foto sebelumnya">
                    <i class="bi bi-chevron-left"></i>
                </button>

                <div class="gallery-lightbox-viewport">
                    <img id="galleryLightboxImage" src="" alt="" class="gallery-lightbox-image">
                </div>

                <button type="button" class="gallery-lightbox-nav next" id="galleryNextBtn" aria-label="Foto berikutnya">
                    <i class="bi bi-chevron-right"></i>
                </button>

                <div class="gallery-lightbox-toolbar">
                    <button type="button" class="btn btn-light" id="zoomOutBtn" aria-label="Perkecil ukuran foto">
                        <i class="bi bi-zoom-out"></i>
                    </button>
                    <button type="button" class="btn btn-light" id="zoomResetBtn" aria-label="Reset ukuran foto">
                        <i class="bi bi-arrows-fullscreen"></i>
                    </button>
                    <button type="button" class="btn btn-light" id="zoomInBtn" aria-label="Perbesar ukuran foto">
                        <i class="bi bi-zoom-in"></i>
                    </button>
                </div>

                <div class="gallery-lightbox-caption" id="galleryLightboxCaption">Kamar</div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modalElement = document.getElementById('kamarGalleryModal');
        if (!modalElement) return;

        const modal = new bootstrap.Modal(modalElement);
        const modalImage = document.getElementById('galleryLightboxImage');
        const modalCaption = document.getElementById('galleryLightboxCaption');
        const prevBtn = document.getElementById('galleryPrevBtn');
        const nextBtn = document.getElementById('galleryNextBtn');
        const zoomInBtn = document.getElementById('zoomInBtn');
        const zoomOutBtn = document.getElementById('zoomOutBtn');
        const zoomResetBtn = document.getElementById('zoomResetBtn');

        const slides = Array.from(document.querySelectorAll('.gallery-lightbox-trigger')).map((trigger) => ({
            src: trigger.dataset.image,
            alt: trigger.dataset.caption || 'Foto kamar'
        }));

        let currentIndex = 0;
        let zoomLevel = 1;

        const renderImage = () => {
            const slide = slides[currentIndex];
            if (!slide) return;

            modalImage.src = slide.src;
            modalImage.alt = slide.alt;
            modalCaption.textContent = slide.alt;
            modalImage.style.transform = `scale(${zoomLevel})`;
        };

        const setZoom = (value) => {
            zoomLevel = Math.min(3, Math.max(1, value));
            modalImage.style.transform = `scale(${zoomLevel})`;
        };

        document.querySelectorAll('.gallery-lightbox-trigger').forEach((trigger, index) => {
            trigger.addEventListener('click', function () {
                currentIndex = Number(this.dataset.index || index);
                zoomLevel = 1;
                renderImage();
                modal.show();
            });
        });

        prevBtn.addEventListener('click', function () {
            currentIndex = (currentIndex - 1 + slides.length) % slides.length;
            zoomLevel = 1;
            renderImage();
        });

        nextBtn.addEventListener('click', function () {
            currentIndex = (currentIndex + 1) % slides.length;
            zoomLevel = 1;
            renderImage();
        });

        zoomInBtn.addEventListener('click', function () {
            setZoom(zoomLevel + 0.25);
        });

        zoomOutBtn.addEventListener('click', function () {
            setZoom(zoomLevel - 0.25);
        });

        zoomResetBtn.addEventListener('click', function () {
            zoomLevel = 1;
            modalImage.style.transform = 'scale(1)';
        });

        modalElement.addEventListener('hidden.bs.modal', function () {
            zoomLevel = 1;
            modalImage.style.transform = 'scale(1)';
        });
    });
</script>
@endsection
