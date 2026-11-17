@extends('layouts.dashboard')

@section('title', 'Dashboard Operasional Pemilik Kost')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h2 class="h3 fw-bold text-secondary mb-1">Dashboard Operasional Kost Griya Ayu</h2>
        <p class="text-muted small mb-0">Pemantauan real-time ketersediaan kamar, antrean validasi, dan keuangan</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('pemilik.kamar.create') }}" class="btn btn-primary-griya btn-sm">
            <i class="bi bi-plus-circle me-1"></i> Tambah Kamar Baru
        </a>
        <a href="{{ route('pemilik.laporan.index') }}" class="btn btn-outline-griya btn-sm">
            <i class="bi bi-file-earmark-bar-graph me-1"></i> Laporan Keuangan
        </a>
    </div>
</div>

<!-- Statistik Minimal Operasional (PRD Section 16) -->
<div class="row g-3 mb-4">
    <!-- Total Kamar -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div>
                <span class="small text-muted fw-medium d-block mb-1">Total Kamar</span>
                <h3 class="fw-bold text-secondary mb-0">{{ $totalKamar }}</h3>
                <small class="text-muted">{{ $kamarTersedia }} Tersedia | {{ $kamarTerisi }} Terisi</small>
            </div>
            <div class="stat-icon" style="background: rgba(37, 99, 235, 0.1); color: var(--brand-primary);">
                <i class="bi bi-door-open"></i>
            </div>
        </div>
    </div>

    <!-- Total Penghuni Aktif -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div>
                <span class="small text-muted fw-medium d-block mb-1">Penghuni Aktif</span>
                <h3 class="fw-bold text-secondary mb-0">{{ $totalPenghuniAktif }}</h3>
                <small class="text-success"><i class="bi bi-check2 me-1"></i>Menyewa resmi</small>
            </div>
            <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--brand-success);">
                <i class="bi bi-people"></i>
            </div>
        </div>
    </div>

    <!-- Booking Menunggu Validasi -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div>
                <span class="small text-muted fw-medium d-block mb-1">Booking Menunggu</span>
                <h3 class="fw-bold text-secondary mb-0">{{ $bookingMenunggu }}</h3>
                @if($bookingMenunggu > 0)
                    <a href="{{ route('pemilik.booking.index', ['status' => 'Menunggu Validasi']) }}" class="small text-warning text-decoration-none fw-semibold">Perlu divalidasi &rarr;</a>
                @else
                    <small class="text-muted">Tidak ada antrean</small>
                @endif
            </div>
            <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: var(--brand-warning);">
                <i class="bi bi-calendar-check"></i>
            </div>
        </div>
    </div>

    <!-- Pembayaran Menunggu Validasi -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div>
                <span class="small text-muted fw-medium d-block mb-1">Pembayaran Menunggu</span>
                <h3 class="fw-bold text-secondary mb-0">{{ $pembayaranMenunggu }}</h3>
                @if($pembayaranMenunggu > 0)
                    <a href="{{ route('pemilik.pembayaran.index', ['status' => 'Menunggu Validasi']) }}" class="small text-danger text-decoration-none fw-semibold">Verifikasi bukti &rarr;</a>
                @else
                    <small class="text-muted">Semua tervalidasi</small>
                @endif
            </div>
            <div class="stat-icon" style="background: rgba(239, 68, 68, 0.1); color: var(--brand-danger);">
                <i class="bi bi-credit-card"></i>
            </div>
        </div>
    </div>

    <!-- Tagihan Belum Lunas -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div>
                <span class="small text-muted fw-medium d-block mb-1">Tagihan Belum Lunas</span>
                <h3 class="fw-bold text-secondary mb-0">{{ $tagihanBelumLunas }}</h3>
                <small class="text-muted">Invoice berjalan</small>
            </div>
            <div class="stat-icon" style="background: rgba(100, 116, 139, 0.1); color: #64748B;">
                <i class="bi bi-receipt"></i>
            </div>
        </div>
    </div>

    <!-- Keluhan Menunggu -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div>
                <span class="small text-muted fw-medium d-block mb-1">Keluhan Menunggu</span>
                <h3 class="fw-bold text-secondary mb-0">{{ $keluhanMenunggu }}</h3>
                @if($keluhanMenunggu > 0)
                    <a href="{{ route('pemilik.keluhan.index', ['status' => 'Menunggu']) }}" class="small text-danger text-decoration-none fw-semibold">Tanggapi keluhan &rarr;</a>
                @else
                    <small class="text-muted">Semua ditangani</small>
                @endif
            </div>
            <div class="stat-icon" style="background: rgba(239, 68, 68, 0.1); color: var(--brand-danger);">
                <i class="bi bi-exclamation-octagon"></i>
            </div>
        </div>
    </div>

    <!-- Pendapatan Bulan Ini -->
    <div class="col-xl-6 col-md-12">
        <div class="stat-card bg-primary text-white border-0">
            <div>
                <span class="small text-white-50 fw-medium d-block mb-1">Pendapatan Terverifikasi Bulan Ini</span>
                <h3 class="fw-bold text-white mb-0">Rp {{ number_format($pendapatanBulanIni, 0, ',', '.') }}</h3>
                <small class="text-white-50">Bulan {{ now()->translatedFormat('F Y') }}</small>
            </div>
            <div class="stat-icon" style="background: rgba(255, 255, 255, 0.2); color: #FFFFFF;">
                <i class="bi bi-cash-stack"></i>
            </div>
        </div>
    </div>
</div>

<!-- Recent Feeds -->
<div class="row g-4">
    <!-- Booking Terbaru -->
    <div class="col-lg-6">
        <div class="card-griya p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-secondary mb-0"><i class="bi bi-calendar-check text-primary me-2"></i>Booking Terbaru</h5>
                <a href="{{ route('pemilik.booking.index') }}" class="small text-primary text-decoration-none">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Pemohon</th>
                            <th>Kamar</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentBookings as $bkg)
                            <tr>
                                <td>
                                    <strong>{{ $bkg->user->name }}</strong>
                                    <div class="text-muted small">{{ $bkg->user->phone }}</div>
                                </td>
                                <td>{{ $bkg->kamar->nomor_kamar ?? '-' }}</td>
                                <td>
                                    @if($bkg->status === 'Menunggu Validasi')
                                        <span class="badge bg-warning text-dark">Validasi</span>
                                    @elseif($bkg->status === 'Menunggu Pembayaran')
                                        <span class="badge bg-primary">Menunggu Bayar</span>
                                    @elseif($bkg->status === 'Selesai')
                                        <span class="badge bg-success">Lunas</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $bkg->status }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('pemilik.booking.show', $bkg->id) }}" class="btn btn-sm btn-outline-griya py-0 px-2">
                                        Periksa
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-3 text-muted">Belum ada booking kamar.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pembayaran Terbaru -->
    <div class="col-lg-6">
        <div class="card-griya p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-secondary mb-0"><i class="bi bi-credit-card text-primary me-2"></i>Pembayaran Masuk</h5>
                <a href="{{ route('pemilik.pembayaran.index') }}" class="small text-primary text-decoration-none">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Pengirim</th>
                            <th>Nominal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentPembayarans as $byr)
                            <tr>
                                <td>
                                    <strong>{{ $byr->user->name }}</strong>
                                    <div class="text-muted small">{{ $byr->jenis_pembayaran }}</div>
                                </td>
                                <td><strong>Rp {{ number_format($byr->nominal, 0, ',', '.') }}</strong></td>
                                <td>
                                    @if($byr->status === 'Lunas')
                                        <span class="badge bg-success">Lunas</span>
                                    @elseif($byr->status === 'Ditolak')
                                        <span class="badge bg-danger">Ditolak</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Perlu Validasi</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('pemilik.pembayaran.show', $byr->id) }}" class="btn btn-sm btn-outline-griya py-0 px-2">
                                        Validasi
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-3 text-muted">Belum ada pembayaran masuk.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Keluhan Terkini -->
    <div class="col-lg-6">
        <div class="card-griya p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-secondary mb-0"><i class="bi bi-exclamation-octagon text-primary me-2"></i>Keluhan Penghuni</h5>
                <a href="{{ route('pemilik.keluhan.index') }}" class="small text-primary text-decoration-none">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Pelapor</th>
                            <th>Masalah</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentKeluhans as $klh)
                            <tr>
                                <td>{{ $klh->user->name }}</td>
                                <td>
                                    <strong>{{ $klh->judul }}</strong>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $klh->status === 'Selesai' ? 'success' : ($klh->status === 'Diproses' ? 'info text-dark' : 'warning text-dark') }}">{{ $klh->status }}</span>
                                </td>
                                <td>
                                    <a href="{{ route('pemilik.keluhan.show', $klh->id) }}" class="btn btn-sm btn-outline-griya py-0 px-2">
                                        Tanggapi
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-3 text-muted">Tidak ada keluhan aktif.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Ulasan & Rating Terbaru -->
    <div class="col-lg-6">
        <div class="card-griya p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-secondary mb-0"><i class="bi bi-star text-primary me-2"></i>Rating & Ulasan Terbaru</h5>
                <a href="{{ route('pemilik.rating.index') }}" class="small text-primary text-decoration-none">Lihat Semua</a>
            </div>
            <div class="list-group list-group-flush small">
                @forelse($recentRatings as $rtg)
                    <div class="list-group-item px-0 py-2 border-0 border-bottom">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong class="text-secondary">{{ $rtg->user->name }} ({{ $rtg->jenis_rating }})</strong>
                            <div class="text-warning">
                                @for($i=1;$i<=5;$i++)
                                    <i class="bi bi-star{{ $i <= $rtg->skor ? '-fill' : '' }}"></i>
                                @endfor
                            </div>
                        </div>
                        <p class="text-muted mb-0 small">"{{ $rtg->komentar ?: 'Tidak ada komentar tertulis.' }}"</p>
                    </div>
                @empty
                    <p class="text-muted text-center py-3 mb-0">Belum ada rating yang diberikan penghuni.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
