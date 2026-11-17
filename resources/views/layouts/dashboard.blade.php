<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="user-id" content="{{ Auth::id() }}">
    <meta name="broadcast-auth-url" content="{{ url('/broadcasting/auth') }}">
    <meta name="notification-url-template" content="{{ route('notifications.open', ['id' => '__NOTIFICATION_ID__']) }}">
    <title>@yield('title', 'Dashboard') — Kost Putri Griya Ayu</title>
    
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Custom Griya Ayu Styles -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    @stack('styles')
</head>
<body class="bg-light">
    <!-- Dashboard Top Header -->
    <header class="navbar navbar-expand-lg bg-white border-bottom sticky-top py-2 px-2 px-sm-3 px-md-4 shadow-sm dashboard-navbar" style="z-index: 1020;">
        <div class="d-flex align-items-center gap-2 gap-sm-3">
            <button class="btn btn-sm btn-light border d-lg-none d-flex align-items-center justify-content-center" type="button" data-bs-toggle="collapse" data-bs-target="#dashboardSidebar" aria-label="Buka menu navigasi" style="width: 36px; height: 36px;">
                <i class="bi bi-list fs-5"></i>
            </button>
            <a href="{{ route('home') }}" class="d-flex align-items-center gap-2 text-decoration-none">
                <div class="stat-icon" style="background: rgba(37, 99, 235, 0.1); color: var(--brand-primary); width: 34px; height: 34px;">
                    <i class="bi bi-buildings"></i>
                </div>
                <div>
                    <span class="navbar-brand-title d-block lh-1">
                        <span class="d-none d-sm-inline">Kost Putri </span>Griya Ayu
                    </span>
                    <span class="navbar-brand-subtitle d-none d-sm-block" style="font-size: 0.65rem;">Management Portal</span>
                </div>
            </a>
        </div>

        <div class="ms-auto d-flex align-items-center gap-2 gap-sm-3">
            <a href="{{ route('home') }}" class="btn btn-sm btn-outline-griya d-none d-md-inline-flex align-items-center gap-1">
                <i class="bi bi-globe"></i> Website Publik
            </a>

            @php
                $unreadNotifications = Auth::user()->unreadNotifications()->count();
                $latestNotifications = Auth::user()->notifications()->latest()->take(5)->get();
            @endphp
            <div class="dropdown">
                <button id="notificationBell" class="btn btn-light border position-relative d-flex align-items-center justify-content-center" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifikasi" style="width: 36px; height: 36px;">
                    <i class="bi bi-bell fs-5 text-primary"></i>
                    <span id="notificationBadge" data-count="{{ $unreadNotifications }}" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger {{ $unreadNotifications > 0 ? '' : 'd-none' }}" style="font-size: 0.62rem;">{{ $unreadNotifications > 99 ? '99+' : $unreadNotifications }}</span>
                </button>
                <div id="notificationDropdown" class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2 p-0" style="width: min(340px, calc(100vw - 1.5rem));">
                    <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                        <strong class="text-secondary">Notifikasi</strong>
                        <a href="{{ route('notifications.index') }}" class="small text-primary text-decoration-none">Lihat semua</a>
                    </div>
                    <div id="notificationDropdownItems">
                    @forelse($latestNotifications as $notification)
                        @php($notificationData = $notification->data)
                        <a href="{{ route('notifications.open', ['id' => $notification->id]) }}" class="dropdown-item px-3 py-2 {{ $notification->read_at ? '' : 'bg-light' }}">
                            <div class="d-flex gap-2 align-items-start">
                                <i class="bi {{ $notification->read_at ? 'bi-envelope-open text-muted' : 'bi-envelope-fill text-primary' }} mt-1"></i>
                                <div class="small text-wrap">
                                    <strong class="d-block text-secondary">{{ $notificationData['title'] ?? 'Notifikasi baru' }}</strong>
                                    <span class="text-muted">{{ $notificationData['message'] ?? '' }}</span>
                                    <small class="d-block text-muted mt-1">{{ $notification->created_at?->diffForHumans() }}</small>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="px-3 py-4 text-center text-muted small">Belum ada notifikasi.</div>
                    @endforelse
                    </div>
                </div>
            </div>

            <div class="dropdown">
                <button class="btn btn-light border rounded-pill d-flex align-items-center gap-1 gap-sm-2 py-1 px-2 px-sm-3" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-circle fs-5 text-primary"></i>
                    <span class="fw-medium small d-none d-md-inline">{{ Auth::user()->name }}</span>
                    <span class="badge bg-primary rounded-pill small d-none d-sm-inline">{{ Auth::user()->role->name ?? 'User' }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-3 mt-2">
                    <li class="px-3 py-2 border-bottom">
                        <strong class="d-block text-secondary small">{{ Auth::user()->name }}</strong>
                        <span class="badge bg-primary rounded-pill small my-1">{{ Auth::user()->role->name ?? 'User' }}</span>
                        <small class="text-muted d-block">{{ Auth::user()->email }}</small>
                    </li>
                    <li>
                        <a class="dropdown-item py-2" href="{{ route('profile') }}">
                            <i class="bi bi-person-gear me-2 text-muted"></i> Profil Saya
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
        </div>
    </header>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar Navigation -->
            <nav id="dashboardSidebar" class="col-lg-3 col-xl-2 d-lg-block collapse sidebar-griya">
                <!-- Header khusus drawer mobile -->
                <div class="d-flex d-lg-none justify-content-between align-items-center pb-3 mb-2 border-bottom border-light border-opacity-10">
                    <div class="d-flex align-items-center gap-2">
                        <div class="stat-icon" style="background: rgba(255, 255, 255, 0.15); color: #fff; width: 32px; height: 32px; border-radius: 6px;">
                            <i class="bi bi-buildings"></i>
                        </div>
                        <div>
                            <span class="text-white fw-bold d-block lh-1" style="font-size: 0.95rem;">Kost Griya Ayu</span>
                            <span class="text-white-50" style="font-size: 0.65rem;">Management Portal</span>
                        </div>
                    </div>
                    <button class="btn btn-sm btn-link text-white text-opacity-75 p-1" type="button" data-bs-toggle="collapse" data-bs-target="#dashboardSidebar" aria-label="Tutup menu navigasi">
                        <i class="bi bi-x-lg fs-5"></i>
                    </button>
                </div>

                <div class="pt-2">
                    @if(Auth::user()->isSuperAdmin())
                        <div class="sidebar-heading">Menu Super Admin</div>
                        <a href="{{ route('admin.dashboard') }}" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-speedometer2"></i> Dashboard Sistem
                        </a>

                        <div class="sidebar-heading mt-3">Operasional</div>
                        @if(Auth::user()->hasPermission('view-kamar') || Auth::user()->hasPermission('manage-kamar'))
                        <a href="{{ route('pengelola.kamar.index') }}" class="sidebar-link {{ request()->routeIs('pengelola.kamar.*') ? 'active' : '' }}">
                            <i class="bi bi-door-open"></i> Kamar
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('manage-penghuni'))
                        <a href="{{ route('pengelola.penghuni.index') }}" class="sidebar-link {{ request()->routeIs('pengelola.penghuni.*') ? 'active' : '' }}">
                            <i class="bi bi-people"></i> Penghuni
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('validate-booking'))
                        <a href="{{ route('pengelola.booking.index') }}" class="sidebar-link {{ request()->routeIs('pengelola.booking.*') ? 'active' : '' }}">
                            <i class="bi bi-calendar-check"></i> Booking
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('validate-pembayaran'))
                        <a href="{{ route('pengelola.pembayaran.index') }}" class="sidebar-link {{ request()->routeIs('pengelola.pembayaran.*') ? 'active' : '' }}">
                            <i class="bi bi-credit-card"></i> Pembayaran
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('manage-tagihan'))
                        <a href="{{ route('pengelola.tagihan.index') }}" class="sidebar-link {{ request()->routeIs('pengelola.tagihan.*') ? 'active' : '' }}">
                            <i class="bi bi-receipt"></i> Tagihan
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('validate-booking'))
                        <a href="{{ route('pengelola.perpanjangan.index') }}" class="sidebar-link {{ request()->routeIs('pengelola.perpanjangan.*') ? 'active' : '' }}">
                            <i class="bi bi-arrow-repeat"></i> Perpanjangan
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('manage-keluhan'))
                        <a href="{{ route('pengelola.keluhan.index') }}" class="sidebar-link {{ request()->routeIs('pengelola.keluhan.*') ? 'active' : '' }}">
                            <i class="bi bi-exclamation-octagon"></i> Keluhan
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('manage-rating'))
                        <a href="{{ route('pengelola.rating.index') }}" class="sidebar-link {{ request()->routeIs('pengelola.rating.*') ? 'active' : '' }}">
                            <i class="bi bi-star"></i> Rating
                        </a>
                        @endif

                        <div class="sidebar-heading mt-3">Manajemen Sistem</div>
                        @if(Auth::user()->hasPermission('manage-users'))
                        <a href="{{ route('admin.users.index') }}" class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                            <i class="bi bi-person-gear"></i> User Management
                        </a>
                        @endif
                        <!-- @if(Auth::user()->hasPermission('manage-roles'))
                        <a href="{{ route('admin.roles.index') }}" class="sidebar-link {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
                            <i class="bi bi-shield-lock"></i> Role & Permissions
                        </a> -->
                        @endif
                        @if(Auth::user()->hasPermission('view-laporan-sistem'))
                        <a href="{{ route('admin.laporan') }}" class="sidebar-link {{ request()->routeIs('admin.laporan*') ? 'active' : '' }}">
                            <i class="bi bi-journal-text"></i> Laporan & Audit
                        </a>
                        @endif
                    @elseif(Auth::user()->isPengelola())
                        <div class="sidebar-heading">Operasional Kost</div>
                        <a href="{{ route('pengelola.dashboard') }}" class="sidebar-link {{ request()->routeIs('pengelola.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-speedometer2"></i> Dashboard Pengelola
                        </a>
                        @if(Auth::user()->hasPermission('view-kamar') || Auth::user()->hasPermission('manage-kamar'))
                        <a href="{{ route('pengelola.kamar.index') }}" class="sidebar-link {{ request()->routeIs('pengelola.kamar.*') ? 'active' : '' }}">
                            <i class="bi bi-door-open"></i> Kelola Kamar
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('manage-penghuni'))
                        <a href="{{ route('pengelola.penghuni.index') }}" class="sidebar-link {{ request()->routeIs('pengelola.penghuni.*') ? 'active' : '' }}">
                            <i class="bi bi-people"></i> Data Penghuni
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('validate-booking'))
                        <a href="{{ route('pengelola.booking.index') }}" class="sidebar-link {{ request()->routeIs('pengelola.booking.*') ? 'active' : '' }}">
                            <i class="bi bi-calendar-check"></i> Validasi Booking
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('validate-pembayaran'))
                        <a href="{{ route('pengelola.pembayaran.index') }}" class="sidebar-link {{ request()->routeIs('pengelola.pembayaran.*') ? 'active' : '' }}">
                            <i class="bi bi-credit-card"></i> Validasi Pembayaran
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('manage-tagihan'))
                        <a href="{{ route('pengelola.tagihan.index') }}" class="sidebar-link {{ request()->routeIs('pengelola.tagihan.*') ? 'active' : '' }}">
                            <i class="bi bi-receipt"></i> Kelola Tagihan
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('validate-booking'))
                        <a href="{{ route('pengelola.perpanjangan.index') }}" class="sidebar-link {{ request()->routeIs('pengelola.perpanjangan.*') ? 'active' : '' }}">
                            <i class="bi bi-arrow-repeat"></i> Perpanjangan (DP)
                        </a>
                        @endif
                        <a href="{{ route('pengelola.keluhan.index') }}" class="sidebar-link {{ request()->routeIs('pengelola.keluhan.*') ? 'active' : '' }}">
                            <i class="bi bi-exclamation-octagon"></i> Keluhan
                        </a>
                        @if(Auth::user()->hasPermission('manage-rating'))
                        <a href="{{ route('pengelola.rating.index') }}" class="sidebar-link {{ request()->routeIs('pengelola.rating.*') ? 'active' : '' }}">
                            <i class="bi bi-star"></i> Rating & Ulasan
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('view-laporan-operasional'))
                        <a href="{{ route('pengelola.laporan.index') }}" class="sidebar-link {{ request()->routeIs('pengelola.laporan.*') ? 'active' : '' }}">
                            <i class="bi bi-file-earmark-bar-graph"></i> Laporan Rekap
                        </a>
                        @endif
                    @elseif(Auth::user()->isPemilikKost())
                        <div class="sidebar-heading">Menu Pemilik Kost</div>
                        <a href="{{ route('pemilik-kost.dashboard') }}" class="sidebar-link {{ request()->routeIs('pemilik-kost.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-speedometer2"></i> Dashboard Monitoring
                        </a>
                        @if(Auth::user()->hasPermission('view-kamar'))
                        <a href="{{ route('pemilik-kost.kamar.index') }}" class="sidebar-link {{ request()->routeIs('pemilik-kost.kamar.*') ? 'active' : '' }}">
                            <i class="bi bi-door-open"></i> Data Kamar
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('view-penghuni-readonly') || Auth::user()->hasPermission('manage-penghuni'))
                        <a href="{{ route('pemilik-kost.penghuni.index') }}" class="sidebar-link {{ request()->routeIs('pemilik-kost.penghuni.*') ? 'active' : '' }}">
                            <i class="bi bi-people"></i> Data Penghuni
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('view-laporan-operasional'))
                        <a href="{{ route('pemilik-kost.laporan.index') }}" class="sidebar-link {{ request()->routeIs('pemilik-kost.laporan.*') ? 'active' : '' }}">
                            <i class="bi bi-file-earmark-bar-graph"></i> Laporan & Keuangan
                        </a>
                        @endif
                    @else
                        <div class="sidebar-heading">Menu Penghuni</div>
                        <a href="{{ route('penghuni.dashboard') }}" class="sidebar-link {{ request()->routeIs('penghuni.dashboard') ? 'active' : '' }}">
                            <i class="bi bi-speedometer2"></i> Dashboard Saya
                        </a>
                        @if(Auth::user()->hasPermission('create-booking'))
                        <a href="{{ route('penghuni.booking.index') }}" class="sidebar-link {{ request()->routeIs('penghuni.booking.*') ? 'active' : '' }}">
                            <i class="bi bi-calendar-check"></i> Pemesanan Kamar
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('create-pembayaran'))
                        <a href="{{ route('penghuni.pembayaran.index') }}" class="sidebar-link {{ request()->routeIs('penghuni.pembayaran.*') ? 'active' : '' }}">
                            <i class="bi bi-credit-card"></i> Pembayaran Saya
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('view-self-tagihan'))
                        <a href="{{ route('penghuni.tagihan.index') }}" class="sidebar-link {{ request()->routeIs('penghuni.tagihan.*') ? 'active' : '' }}">
                            <i class="bi bi-receipt"></i> Tagihan Sewa
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('create-booking'))
                        <a href="{{ route('penghuni.perpanjangan.index') }}" class="sidebar-link {{ request()->routeIs('penghuni.perpanjangan.*') ? 'active' : '' }}">
                            <i class="bi bi-arrow-repeat"></i> Perpanjangan Sewa
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('create-keluhan'))
                        <a href="{{ route('penghuni.keluhan.index') }}" class="sidebar-link {{ request()->routeIs('penghuni.keluhan.*') ? 'active' : '' }}">
                            <i class="bi bi-exclamation-octagon"></i> Keluhan Fasilitas
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('create-rating'))
                        <a href="{{ route('penghuni.rating.index') }}" class="sidebar-link {{ request()->routeIs('penghuni.rating.*') ? 'active' : '' }}">
                            <i class="bi bi-star"></i> Penilaian / Rating
                        </a>
                        @endif
                        @if(Auth::user()->hasPermission('view-self-profile'))
                        <a href="{{ route('penghuni.riwayat.index') }}" class="sidebar-link {{ request()->routeIs('penghuni.riwayat.*') ? 'active' : '' }}">
                            <i class="bi bi-clock-history"></i> Riwayat & Arsip
                        </a>
                        @endif
                    @endif

                    <div class="sidebar-heading mt-4">Akun</div>
                    <a href="{{ route('profile') }}" class="sidebar-link {{ request()->routeIs('profile') ? 'active' : '' }}">
                        <i class="bi bi-person-gear"></i> Pengaturan Profil
                    </a>
                </div>
            </nav>

            <!-- Mobile Sidebar Backdrop -->
            <div class="sidebar-backdrop d-lg-none" data-bs-toggle="collapse" data-bs-target="#dashboardSidebar" aria-label="Tutup menu"></div>

            <!-- Main Workspace Area -->
            <main class="col-lg-9 col-xl-10 px-md-4 py-4">
                <!-- Flash Messages -->
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

                @yield('content')
            </main>
        </div>
    </div>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    @vite('resources/js/app.js')
    @stack('scripts')
</body>
</html>
