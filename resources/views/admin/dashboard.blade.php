@extends('layouts.dashboard')

@section('title', 'Dashboard Super Admin')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h2 class="h3 fw-bold text-secondary mb-1">Dashboard Super Admin</h2>
        <p class="text-muted small mb-0">Pusat kendali akun pengguna, hak akses peran (RBAC), dan audit sistem</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.users.create') }}" class="btn btn-primary-griya btn-sm">
            <i class="bi bi-person-plus me-1"></i> Tambah User Baru
        </a>
        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-griya btn-sm">
            <i class="bi bi-shield-lock me-1"></i> Kelola Hak Akses Role
        </a>
    </div>
</div>

<!-- Statistik Sistem -->
<div class="row g-3 mb-3">
    <!-- Total User -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div>
                <span class="small text-muted fw-medium d-block mb-1">Total User Sistem</span>
                <h3 class="fw-bold text-secondary mb-0">{{ $totalUser }}</h3>
                <small class="text-muted">Semua hak akses terdaftar</small>
            </div>
            <div class="stat-icon" style="background: rgba(37, 99, 235, 0.1); color: var(--brand-primary);">
                <i class="bi bi-person-badge"></i>
            </div>
        </div>
    </div>

    <!-- Total Kamar -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div>
                <span class="small text-muted fw-medium d-block mb-1">Total Kamar</span>
                <h3 class="fw-bold text-secondary mb-0">{{ $totalKamar }}</h3>
                <small class="text-muted">Total unit kamar</small>
            </div>
            <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--brand-success);">
                <i class="bi bi-door-closed"></i>
            </div>
        </div>
    </div>

    <!-- Total Penghuni -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div>
                <span class="small text-muted fw-medium d-block mb-1">Total Penghuni</span>
                <h3 class="fw-bold text-secondary mb-0">{{ $totalPenghuni }}</h3>
                <small class="text-muted">Akun dengan role penghuni</small>
            </div>
            <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: var(--brand-warning);">
                <i class="bi bi-people"></i>
            </div>
        </div>
    </div>

    <!-- Total Booking -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div>
                <span class="small text-muted fw-medium d-block mb-1">Total Booking</span>
                <h3 class="fw-bold text-secondary mb-0">{{ $totalBooking }}</h3>
                <small class="text-muted">Pemesanan sepanjang waktu</small>
            </div>
            <div class="stat-icon" style="background: rgba(139, 92, 246, 0.1); color: #8B5CF6;">
                <i class="bi bi-calendar-check"></i>
            </div>
        </div>
    </div>
</div>

<!-- Statistik Operasional Monitoring -->
<div class="row g-3 mb-4">
    <!-- Kamar Tersedia -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div>
                <span class="small text-muted fw-medium d-block mb-1">Kamar Tersedia</span>
                <h3 class="fw-bold text-secondary mb-0">{{ $kamarTersedia }}</h3>
                <small class="text-muted">Siap huni</small>
            </div>
            <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--brand-success);">
                <i class="bi bi-check-circle"></i>
            </div>
        </div>
    </div>

    <!-- Kamar Terisi -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div>
                <span class="small text-muted fw-medium d-block mb-1">Kamar Terisi</span>
                <h3 class="fw-bold text-secondary mb-0">{{ $kamarTerisi }}</h3>
                <small class="text-muted">Sedang disewa</small>
            </div>
            <div class="stat-icon" style="background: rgba(239, 68, 68, 0.1); color: var(--brand-danger);">
                <i class="bi bi-door-open"></i>
            </div>
        </div>
    </div>

    <!-- Pembayaran Menunggu -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div>
                <span class="small text-muted fw-medium d-block mb-1">Pembayaran Menunggu</span>
                <h3 class="fw-bold text-secondary mb-0">{{ $pembayaranMenunggu }}</h3>
                <small class="text-muted">Butuh divalidasi</small>
            </div>
            <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: var(--brand-warning);">
                <i class="bi bi-wallet2"></i>
            </div>
        </div>
    </div>

    <!-- Tagihan Belum Lunas -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div>
                <span class="small text-muted fw-medium d-block mb-1">Tagihan Belum Lunas</span>
                <h3 class="fw-bold text-secondary mb-0">{{ $tagihanBelumLunas }}</h3>
                <small class="text-muted">Menunggu pelunasan</small>
            </div>
            <div class="stat-icon" style="background: rgba(239, 68, 68, 0.1); color: var(--brand-danger);">
                <i class="bi bi-receipt"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Pengguna Terbaru -->
    <div class="col-lg-6">
        <div class="card-griya p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-secondary mb-0"><i class="bi bi-people text-primary me-2"></i>Pengguna Terbaru</h5>
                <a href="{{ route('admin.users.index') }}" class="small text-primary text-decoration-none">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nama</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentUsers as $usr)
                            <tr>
                                <td>
                                    <strong>{{ $usr->name }}</strong>
                                    <div class="text-muted small">{{ $usr->email }}</div>
                                </td>
                                <td><span class="badge bg-light text-primary border">{{ $usr->role->name ?? '-' }}</span></td>
                                <td>
                                    <span class="badge bg-{{ $usr->status === 'Aktif' ? 'success' : 'danger' }}">{{ $usr->status }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('admin.users.edit', $usr->id) }}" class="btn btn-sm btn-outline-griya py-0 px-2">Edit</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-3 text-muted">Belum ada user terdaftar.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Log Aktivitas Sistem (Audit Trail) -->
    <div class="col-lg-6">
        <div class="card-griya p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-secondary mb-0"><i class="bi bi-clock-history text-primary me-2"></i>Audit Log Aktivitas Sistem</h5>
                <a href="{{ route('admin.laporan') }}" class="small text-primary text-decoration-none">Lihat Semua</a>
            </div>
            <div class="list-group list-group-flush small">
                @forelse($recentAktivitas as $akt)
                    <div class="list-group-item px-0 py-2 border-0 border-bottom">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong class="text-secondary">{{ $akt->judul }}</strong>
                            <small class="text-muted">{{ $akt->created_at->diffForHumans() }}</small>
                        </div>
                        <div class="text-muted small">
                            <span class="badge bg-light text-secondary border me-1">{{ $akt->user->name ?? 'Sistem' }}</span>
                            {{ $akt->deskripsi }}
                        </div>
                    </div>
                @empty
                    <p class="text-muted text-center py-3 mb-0">Belum ada log aktivitas tercatat.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
