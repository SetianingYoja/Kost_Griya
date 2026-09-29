<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Kost Putri Griya Ayu — Hunian Eksklusif & Nyaman')</title>
    <meta name="description" content="@yield('meta_description', 'Sistem Informasi Manajemen Kost Putri Griya Ayu. Hunian eksklusif, aman, nyaman, dan modern untuk mahasiswi & karyawati di Sleman, Yogyakarta.')">

    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom Griya Ayu Styles -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    @stack('styles')
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-griya sticky-top py-3">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="{{ route('home') }}">
                <img src="{{ asset('images/logo-griya-ayu.svg') }}" alt="Griya Ayu Logo" style="height: 52px; width: auto; display: block;">
            </a>

            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
                <i class="bi bi-list fs-2 text-primary"></i>
            </button>

            <div class="collapse navbar-collapse" id="navbarMain">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link nav-link-griya {{ request()->routeIs('home') ? 'active' : '' }}" href="{{ route('home') }}">
                            <i class="bi bi-house-door me-1"></i> Beranda
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-griya {{ request()->routeIs('kamar.*') ? 'active' : '' }}" href="{{ route('kamar.index') }}">
                            <i class="bi bi-door-open me-1"></i> Pilihan Kamar
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link nav-link-griya {{ request()->routeIs('tentang') ? 'active' : '' }}" href="{{ route('tentang') }}">
                            <i class="bi bi-info-circle me-1"></i> Tentang Kami
                        </a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-2">
                    @auth
                        @php
                            $dashboardRoute = route('penghuni.dashboard');
                            if (Auth::user()->isSuperAdmin()) {
                                $dashboardRoute = route('admin.dashboard');
                            } elseif (Auth::user()->isPemilik()) {
                                $dashboardRoute = route('pemilik.dashboard');
                            }
                        @endphp
                        <div class="dropdown">
                            <button class="btn btn-outline-griya dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i>
                                <span>{{ Auth::user()->name }}</span>
                                <span class="badge bg-primary ms-1">{{ Auth::user()->role->name ?? 'User' }}</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2">
                                <li>
                                    <a class="dropdown-item py-2" href="{{ $dashboardRoute }}">
                                        <i class="bi bi-speedometer2 me-2 text-primary"></i> Dashboard Saya
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2" href="{{ route('profile') }}">
                                        <i class="bi bi-person-gear me-2 text-muted"></i> Pengaturan Profil
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item py-2 text-danger">
                                            <i class="bi bi-box-arrow-right me-2"></i> Keluar (Logout)
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-outline-griya">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Masuk
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-primary-griya">
                            <i class="bi bi-person-plus me-1"></i> Daftar
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Flash Messages -->
    <div class="container mt-3">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 d-flex align-items-center" role="alert">
                <i class="bi bi-check-circle-fill fs-5 me-2 text-success"></i>
                <div>{{ session('success') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 d-flex align-items-center" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5 me-2 text-danger"></i>
                <div>{{ session('error') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show border-0 shadow-sm rounded-3 d-flex align-items-center" role="alert">
                <i class="bi bi-info-circle-fill fs-5 me-2 text-primary"></i>
                <div>{{ session('info') }}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3" role="alert">
                <div class="d-flex align-items-center mb-1">
                    <i class="bi bi-exclamation-circle-fill fs-5 me-2 text-danger"></i>
                    <strong>Mohon periksa kesalahan input berikut:</strong>
                </div>
                <ul class="mb-0 ps-3">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    <!-- Main Content -->
    <main class="flex-grow-1">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer-griya pt-5 pb-4 mt-5">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="mb-3">
                        <img src="{{ asset('images/logo-griya-ayu.svg') }}" alt="Griya Ayu Logo" style="height: 60px; width: auto; filter: brightness(0) invert(1); display: block;">
                    </div>
                    <p class="text-secondary small leading-relaxed mb-3">
                        Hunian kost putri modern, aman, dan nyaman di kawasan strategis Purwokerto, Banyumas. Pilihan tepat bagi mahasiswi dan karyawati dengan fasilitas lengkap dan lingkungan kondusif.
                    </p>
                    <div class="d-flex gap-2">
                        <a href="https://wa.me/6282242645466" target="_blank" class="btn btn-sm btn-outline-light rounded-circle" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-whatsapp"></i>
                        </a>
                        <a href="mailto:info@griyaayu.com" class="btn btn-sm btn-outline-light rounded-circle" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-envelope"></i>
                        </a>
                        <a href="https://maps.app.goo.gl/NZ29iPt4KUSdmH3Z8" target="_blank" class="btn btn-sm btn-outline-light rounded-circle" style="width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-geo-alt"></i>
                        </a>
                    </div>
                </div>

                <div class="col-lg-2 col-md-6">
                    <h5 class="mb-3">Navigasi</h5>
                    <ul class="list-unstyled d-flex flex-column gap-2 small">
                        <li><a href="{{ route('home') }}" class="footer-link"><i class="bi bi-chevron-right me-1 small"></i> Beranda</a></li>
                        <li><a href="{{ route('kamar.index') }}" class="footer-link"><i class="bi bi-chevron-right me-1 small"></i> Pilihan Kamar</a></li>
                        <li><a href="{{ route('tentang') }}" class="footer-link"><i class="bi bi-chevron-right me-1 small"></i> Tentang Kami</a></li>
                        <li><a href="{{ route('login') }}" class="footer-link"><i class="bi bi-chevron-right me-1 small"></i> Portal Masuk</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h5 class="mb-3">Tipe Kamar</h5>
                    <ul class="list-unstyled d-flex flex-column gap-2 small">
                        <li><span class="text-secondary"><i class="bi bi-check2 text-primary me-2"></i> Tipe Standar (Rp 950rb/bln)</span></li>
                        <li><span class="text-secondary"><i class="bi bi-check2 text-primary me-2"></i> Tipe Deluxe (Rp 1.35jt/bln)</span></li>
                        <li><span class="text-secondary"><i class="bi bi-check2 text-primary me-2"></i> Tipe VIP Suite (Rp 1.85jt/bln)</span></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h5 class="mb-3">Kontak & Lokasi</h5>
                    <ul class="list-unstyled d-flex flex-column gap-2 small text-secondary">
                        <li class="d-flex align-items-start gap-2">
                            <i class="bi bi-geo-alt-fill text-primary mt-1"></i>
                            <span>J62P+XVC, Jl. Kenanga 4, RT.8/RW.2, Sumampir Kulon, Sumampir, Kulon, Kabupaten Banyumas, Jawa Tengah 53125</span>
                        </li>
                        <li class="d-flex align-items-center gap-2">
                            <i class="bi bi-whatsapp text-primary"></i>
                            <span>+62 822-4264-5466</span>
                        </li>
                        <li class="d-flex align-items-center gap-2">
                            <i class="bi bi-clock-fill text-primary"></i>
                            <span>Layanan Resepsionis: 08.00 - 20.00 WIB</span>
                        </li>
                    </ul>
                </div>
            </div>

            <hr class="border-secondary my-4 opacity-25">

            <div class="d-flex flex-column flex-md-row justify-content-between align-items-center small text-secondary">
                <div>&copy; {{ date('Y') }} Kost Putri Griya Ayu. Seluruh hak cipta dilindungi undang-undang.</div>
                <div class="mt-2 mt-md-0">Modern Elegant Boarding House Management System</div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
