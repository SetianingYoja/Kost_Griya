@extends('layouts.app')
@section('title', 'Kost Putri Griya Ayu — Modern Elegant Boarding House')
@section('content')

<section class="hero-section">
    <div class="container">
        <div class="row align-items-center g-5">
            <div class="col-lg-7">
                <div class="badge-tagline mb-3">
                    <i class="bi bi-shield-check"></i>
                    <span>Khusus Putri — Aman, Nyaman, & Tenang</span>
                </div>
                <h1 class="display-4 fw-bold mb-3 text-dark" style="font-family: var(--font-heading); line-height: 1.15;">
                    Hunian Kost Putri Modern & Elegan di Purwokerto, Banyumas
                </h1>
                <p class="lead text-muted mb-4" style="font-size: 1.08rem; font-weight: 400;">
                    Selamat datang di <strong>Kost Putri Griya Ayu</strong>. Penginapan kost eksklusif bernuansa asri dan modern, dirancang khusus untuk kenyamanan belajar mahasiswi serta istirahat optimal para karyawati dengan fasilitas terlengkap
                </p>
                <div class="d-flex flex-wrap gap-3 mb-4">
                    <a href="{{ route('kamar.index') }}" class="btn btn-primary-griya btn-lg px-4 py-3">
                        <i class="bi bi-door-open me-2"></i> Lihat Pilihan Kamar
                    </a>
                    <a href="https://wa.me/6282242645466" target="_blank" class="btn btn-outline-griya btn-lg px-4 py-3">
                        <i class="bi bi-whatsapp me-2"></i> Konsultasi WhatsApp
                    </a>
                </div>
                <div class="d-flex align-items-center gap-4 pt-2">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                        <span class="small fw-semibold text-secondary">CCTV 24 Jam</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                        <span class="small fw-semibold text-secondary">WiFi High-Speed</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill text-success fs-5"></i>
                        <span class="small fw-semibold text-secondary">Dekat Kampus</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="position-relative">
                    <div class="card-griya border-0 shadow-lg overflow-hidden">
                        <div class="room-img-wrapper d-flex align-items-center justify-content-center" style="height: 340px; background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 50%, #BFDBFE 100%);">
                            <img src="{{ asset('images/logo-griya-ayu.svg') }}" alt="Logo Griya Ayu" style="width: 72%; max-width: 300px; height: auto; display: block; filter: drop-shadow(0 8px 24px rgba(37,99,235,0.18));">
                        </div>
                        <div class="p-4 bg-white">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge bg-primary px-3 py-2 rounded-pill">Status Terkini</span>
                                <span class="text-success fw-bold small"><i class="bi bi-circle-fill fs-6 me-1"></i> Tersedia {{ $totalTersedia }} dari {{ $totalKamar }} Kamar</span>
                            </div>
                            <h5 class="fw-bold text-secondary mb-1">Kost Putri Griya Ayu</h5>
                            <p class="text-muted small mb-0">J62P+XVC, Jl. Kenanga 4, RT.8/RW.2, Sumampir Kulon, Sumampir, Kulon, Kabupaten Banyumas, Jawa Tengah 53125</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- Kamar Unggulan Section -->
<section class="py-5 bg-light">
    <div class="container py-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4">
            <div>
                <span class="badge-tagline mb-2">Koleksi Kamar</span>
                <h2 class="display-6 fw-bold text-dark mb-1">Pilihan Kamar Kost Terbaik</h2>
                <p class="text-muted mb-0">Temukan tipe kamar yang paling sesuai dengan preferensi dan kebutuhan Anda</p>
            </div>
            <div class="mt-3 mt-md-0">
                <a href="{{ route('kamar.index') }}" class="btn btn-outline-griya">
                    Lihat Semua Kamar ({{ $totalKamar }}) <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
        </div>

        <div class="row g-4">
            @forelse($kamarUnggulan as $kamar)
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
                            <h4 class="fw-bold text-dark mb-2">{{ $kamar->nomor_kamar }}</h4>
                            <p class="text-muted small mb-3 flex-grow-1">
                                {{ Str::limit($kamar->fasilitas ?? $kamar->deskripsi, 85) }}
                            </p>
                            <div class="d-flex justify-content-between align-items-center pt-3 border-top mt-auto">
                                <div>
                                    <span class="small text-muted d-block">Mulai dari</span>
                                    <span class="price-tag">Rp {{ number_format($kamar->harga, 0, ',', '.') }}</span>
                                    <span class="small text-muted">/bln</span>
                                </div>
                                <a href="{{ route('kamar.detail', $kamar->id) }}" class="btn btn-sm btn-primary-griya px-3">
                                    Detail Kamar <i class="bi bi-chevron-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <p class="text-muted">Data kamar belum tersedia.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

<!-- Keunggulan Fasilitas Section -->
<section class="py-5 bg-white border-bottom">
    <div class="container py-4">
        <div class="text-center max-w-700 mx-auto mb-5">
            <span class="badge-tagline mb-2">Kenapa Memilih Kami?</span>
            <h2 class="display-6 fw-bold text-secondary">Kenyamanan & Keamanan Prioritas Utama</h2>
            <p class="text-muted">Kami menghadirkan pengalaman hunian kost yang tenang, higienis, dan terkelola secara profesional</p>
        </div>

        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="p-4 rounded-4 bg-light h-100 border transition-all">
                    <div class="stat-icon mb-3" style="background: rgba(37, 99, 235, 0.1); color: var(--brand-primary); width: 50px; height: 50px;">
                        <i class="bi bi-shield-lock fs-4"></i>
                    </div>
                    <h5 class="fw-bold text-secondary mb-2">Keamanan Terjamin 24 Jam</h5>
                    <p class="text-muted small mb-0">Dilengkapi kamera pengawas CCTV 24 jam di seluruh area umum, akses gerbang terkontrol, dan penjaga malam profesional</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="p-4 rounded-4 bg-light h-100 border transition-all">
                    <div class="stat-icon mb-3" style="background: rgba(16, 185, 129, 0.1); color: var(--brand-success); width: 50px; height: 50px;">
                        <i class="bi bi-wifi fs-4"></i>
                    </div>
                    <h5 class="fw-bold text-secondary mb-2">Internet Cepat Dedicated</h5>
                    <p class="text-muted small mb-0">Koneksi WiFi serat optik berkecepatan tinggi di setiap lantai untuk menunjang perkuliahan daring, tugas, dan hiburan</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="p-4 rounded-4 bg-light h-100 border transition-all">
                    <div class="stat-icon mb-3" style="background: rgba(245, 158, 11, 0.1); color: var(--brand-warning); width: 50px; height: 50px;">
                        <i class="bi bi-cup-hot fs-4"></i>
                    </div>
                    <h5 class="fw-bold text-secondary mb-2">Dapur & Kulkas Bersama</h5>
                    <p class="text-muted small mb-0">Dapur lengkap dengan kompor gas, dispenser air minum higienis, serta kulkas bersama yang selalu terjaga kebersihannya</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="p-4 rounded-4 bg-light h-100 border transition-all">
                    <div class="stat-icon mb-3" style="background: rgba(239, 68, 68, 0.1); color: var(--brand-danger); width: 50px; height: 50px;">
                        <i class="bi bi-geo-alt fs-4"></i>
                    </div>
                    <h5 class="fw-bold text-secondary mb-2">Lokasi Super Strategis</h5>
                    <p class="text-muted small mb-0">berada di kawasan yang mudah diakses dan dekat dengan berbagai fasilitas pendukung kebutuhan penghuni, seperti tempat makan, minimarket, fasilitas pendidikan, serta layanan umum</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="p-4 rounded-4 bg-light h-100 border transition-all">
                    <div class="stat-icon mb-3" style="background: rgba(59, 130, 246, 0.1); color: var(--brand-info); width: 50px; height: 50px;">
                        <i class="bi bi-droplet-half fs-4"></i>
                    </div>
                    <h5 class="fw-bold text-secondary mb-2">Air Bersih & Water Heater</h5>
                    <p class="text-muted small mb-0">Suplai air bersih melimpah dari sumur bor dalam berkualitas, serta opsi fasilitas kamar mandi dengan pemanas air (water heater)</p>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="p-4 rounded-4 bg-light h-100 border transition-all">
                    <div class="stat-icon mb-3" style="background: rgba(139, 92, 246, 0.1); color: #8B5CF6; width: 50px; height: 50px;">
                        <i class="bi bi-phone fs-4"></i>
                    </div>
                    <h5 class="fw-bold text-secondary mb-2">Layanan Digital Terpadu</h5>
                    <p class="text-muted small mb-0">Booking online, konfirmasi pembayaran, cek tagihan, hingga pelaporan keluhan fasilitas ditangani cepat via sistem web</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Rating & Ulasan Section -->
<section class="py-5 bg-white border-bottom">
    <div class="container py-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-end mb-4">
            <div>
                <span class="badge-tagline mb-2">Testimoni Penghuni</span>
                <h2 class="display-6 fw-bold text-secondary mb-1">Rating & Ulasan Kost</h2>
            </div>
            <div class="mt-3 mt-md-0">
                <div class="d-flex align-items-center gap-2 bg-light rounded-pill px-3 py-2 border">
                    <i class="bi bi-star-fill text-warning"></i>
                    <span class="fw-bold text-secondary">{{ number_format($avgRating ?? 0, 1) }} / 5.0</span>
                    <span class="text-muted small">({{ $totalReviews ?? 0 }} ulasan)</span>
                </div>
            </div>
        </div>

        <div class="row g-4">
            @forelse($recentRatings as $rating)
                <div class="col-lg-3 col-md-6">
                    <div class="card-griya h-100 p-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div>
                                <strong class="d-block text-secondary">{{ $rating->user->name ?? 'Penghuni' }}</strong>
                                <small class="text-muted">Kamar {{ $rating->kamar->nomor_kamar ?? '-' }}</small>
                            </div>
                            <div class="text-warning small">
                                @for($i = 1; $i <= 5; $i++)
                                    <i class="bi bi-star{{ $i <= ($rating->skor ?? 0) ? '-fill' : '' }}"></i>
                                @endfor
                            </div>
                        </div>
                        <p class="text-muted small mb-0">{{ $rating->komentar ?: 'Penghuni menyukai kenyamanan dan layanan kost ini.' }}</p>
                        @if($rating->balasan)
                            <div class="mt-3 p-2 bg-light rounded-3 border-start border-3 border-primary small">
                                <span class="fw-semibold text-primary d-block mb-1" style="font-size: 0.775rem;">
                                    <i class="bi bi-reply-fill me-1"></i>Respon Pemilik Kost:
                                </span>
                                <p class="text-muted small mb-0">{{ $rating->balasan }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="text-center py-5 text-muted">Belum ada ulasan valid dari penghuni aktif.</div>
                </div>
            @endforelse
        </div>
    </div>
</section>

<!-- Lokasi & Kontak Section -->
<section class="py-5 bg-white border-top">
    <div class="container py-4">
        <div class="row g-5 align-items-center">
            <div class="col-lg-6">
                <span class="badge-tagline mb-2">Lokasi & Kontak</span>
                <h2 class="display-6 fw-bold text-secondary mb-3">Kunjungi Kami di Purwokerto, Banyumas</h2>
            <p class="text-muted mb-4">
                Kost Putri Griya Ayu berlokasi strategis di kawasan Purwokerto, dekat dengan Universitas Amikom Purwokerto, UIN SAIZU, Universitas Jenderal Soedirman (Unsoed), dan Polresta Banyumas. Hanya sekitar 10 menit menuju Stasiun Purwokerto, sehingga memudahkan mobilitas penghuni untuk kuliah, bekerja, maupun bepergian.
            </p>

                <div class="d-flex flex-column gap-3 mb-4">
                    <div class="d-flex align-items-start gap-3">
                        <div class="stat-icon bg-light text-primary flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="bi bi-geo-alt-fill fs-5"></i>
                        </div>
                        <div>
                            <strong class="d-block text-secondary">Alamat Lengkap</strong>
                            <span class="text-muted small">J62P+XVC, Jl. Kenanga 4, RT.8/RW.2, Sumampir Kulon, Sumampir, Kulon, Kabupaten Banyumas, Jawa Tengah 53125</span>
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3">
                        <div class="stat-icon bg-light text-success flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="bi bi-whatsapp fs-5"></i>
                        </div>
                        <div>
                            <strong class="d-block text-secondary">WhatsApp Pengelola</strong>
                            <span class="text-muted small">+62 822-4264-5466 (Respon Cepat 08.00 - 20.00 WIB)</span>
                        </div>
                    </div>

                    <div class="d-flex align-items-start gap-3">
                        <div class="stat-icon bg-light text-info flex-shrink-0" style="width: 44px; height: 44px;">
                            <i class="bi bi-envelope-fill fs-5"></i>
                        </div>
                        <div>
                            <strong class="d-block text-secondary">Email Resmi</strong>
                            <span class="text-muted small">info@griyaayu.com</span>
                        </div>
                    </div>
                </div>

                <a href="https://wa.me/6282242645466" target="_blank" class="btn btn-primary-griya">
                    <i class="bi bi-whatsapp me-2"></i> Jadwalkan Survei Kamar
                </a>
            </div>

            <div class="col-lg-6">
                <div class="card-griya p-2 overflow-hidden shadow-sm">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3956.609726232992!2d109.23455517414891!3d-7.397551672833438!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e655effc1bf7461%3A0x34c57e5e2c5a5f19!2sKost%20Putri%20GRIYA%20AYU!5e0!3m2!1sid!2sid!4v1789367540498!5m2!1sid!2sid" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="strict-origin-when-cross-origin"></iframe>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
