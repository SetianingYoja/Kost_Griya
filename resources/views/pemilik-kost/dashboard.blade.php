@extends('layouts.dashboard')

@section('title', 'Dashboard Pemilik Kost (Monitoring & Laporan)')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h2 class="h3 fw-bold text-secondary mb-0">Dashboard Monitoring Pemilik Kost</h2>
            <span class="badge bg-secondary-subtle text-secondary border px-2 py-1 small"><i class="bi bi-eye me-1"></i>Read-Only</span>
        </div>
        <p class="text-muted small mb-0">Pemantauan real-time ketersediaan kamar, statistik keuangan, dan ringkasan operasional</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('pemilik-kost.kamar.index') }}" class="btn btn-outline-griya btn-sm">
            <i class="bi bi-door-open me-1"></i> Data Kamar
        </a>
        <a href="{{ route('pemilik-kost.penghuni.index') }}" class="btn btn-outline-griya btn-sm">
            <i class="bi bi-people me-1"></i> Data Penghuni
        </a>
        <a href="{{ route('pemilik-kost.laporan.index') }}" class="btn btn-primary-griya btn-sm">
            <i class="bi bi-file-earmark-bar-graph me-1"></i> Laporan Keuangan
        </a>
    </div>
</div>

<!-- Statistik Pemantauan -->
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

    <!-- Pendapatan Bulan Ini -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div>
                <span class="small text-muted fw-medium d-block mb-1">Pendapatan Bulan Ini</span>
                <h4 class="fw-bold text-success mb-0">Rp {{ number_format($pendapatanBulanIni, 0, ',', '.') }}</h4>
                <small class="text-muted">Total penerimaan</small>
            </div>
            <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--brand-success);">
                <i class="bi bi-wallet2"></i>
            </div>
        </div>
    </div>

    <!-- Pendapatan Tahun Ini -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div>
                <span class="small text-muted fw-medium d-block mb-1">Pendapatan Tahun Ini</span>
                <h4 class="fw-bold text-primary mb-0">Rp {{ number_format($totalPendapatanTahunIni, 0, ',', '.') }}</h4>
                <small class="text-muted">Tahun {{ date('Y') }}</small>
            </div>
            <div class="stat-icon" style="background: rgba(37, 99, 235, 0.1); color: var(--brand-primary);">
                <i class="bi bi-graph-up-arrow"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <!-- Status Operasional Berjalan -->
    <div class="col-xl-4 col-md-6">
        <div class="stat-card">
            <div>
                <span class="small text-muted fw-medium d-block mb-1">Booking Menunggu Validasi</span>
                <h3 class="fw-bold text-secondary mb-0">{{ $bookingMenunggu }}</h3>
                <small class="text-muted">Ditangani oleh Pengelola</small>
            </div>
            <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: var(--brand-warning);">
                <i class="bi bi-calendar-check"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6">
        <div class="stat-card">
            <div>
                <span class="small text-muted fw-medium d-block mb-1">Tagihan Belum Lunas</span>
                <h3 class="fw-bold text-secondary mb-0">{{ $tagihanBelumLunas }}</h3>
                <small class="text-muted">Invoice sewa berjalan</small>
            </div>
            <div class="stat-icon" style="background: rgba(100, 116, 139, 0.1); color: #64748B;">
                <i class="bi bi-receipt"></i>
            </div>
        </div>
    </div>

    <div class="col-xl-4 col-md-6">
        <div class="stat-card">
            <div>
                <span class="small text-muted fw-medium d-block mb-1">Keluhan Belum Selesai</span>
                <h3 class="fw-bold text-secondary mb-0">{{ $keluhanMenunggu }}</h3>
                <small class="text-muted">Fasilitas kost</small>
            </div>
            <div class="stat-icon" style="background: rgba(239, 68, 68, 0.1); color: var(--brand-danger);">
                <i class="bi bi-exclamation-octagon"></i>
            </div>
        </div>
    </div>
</div>

<!-- Antrean & Ringkasan Aktivitas (Read-Only) -->
<div class="row g-4 mb-4">
    <!-- Booking Terbaru -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100 rounded-3">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-secondary mb-0">Booking Kamar Terbaru</h5>
                <span class="badge bg-light text-muted border">Read-Only</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>Pemesan</th>
                            <th>Kamar</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @forelse($recentBookings as $booking)
                        <tr>
                            <td>
                                <span class="fw-semibold d-block text-secondary">{{ $booking->user->name ?? '-' }}</span>
                                <small class="text-muted">{{ $booking->kode_booking }}</small>
                            </td>
                            <td>Kamar {{ $booking->kamar->nomor_kamar ?? '-' }}</td>
                            <td>Rp {{ number_format($booking->total_harga, 0, ',', '.') }}</td>
                            <td>
                                @if($booking->status === 'Menunggu Validasi')
                                    <span class="badge bg-warning text-dark">{{ $booking->status }}</span>
                                @elseif($booking->status === 'Disetujui' || $booking->status === 'Aktif')
                                    <span class="badge bg-success">{{ $booking->status }}</span>
                                @else
                                    <span class="badge bg-secondary">{{ $booking->status }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">Belum ada booking terbaru.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Pembayaran Masuk Terbaru -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100 rounded-3">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-secondary mb-0">Pembayaran Terbaru</h5>
                <span class="badge bg-light text-muted border">Read-Only</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>Pengirim</th>
                            <th>Jenis</th>
                            <th>Nominal</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @forelse($recentPembayarans as $pembayaran)
                        <tr>
                            <td>
                                <span class="fw-semibold d-block text-secondary">{{ $pembayaran->user->name ?? '-' }}</span>
                                <small class="text-muted">{{ $pembayaran->metode_pembayaran }}</small>
                            </td>
                            <td>{{ $pembayaran->jenis_pembayaran }}</td>
                            <td class="fw-semibold text-secondary">Rp {{ number_format($pembayaran->nominal, 0, ',', '.') }}</td>
                            <td>
                                @if($pembayaran->status === 'Lunas')
                                    <span class="badge bg-success">Lunas</span>
                                @elseif($pembayaran->status === 'Menunggu Validasi')
                                    <span class="badge bg-warning text-dark">Menunggu</span>
                                @else
                                    <span class="badge bg-danger">{{ $pembayaran->status }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">Belum ada pembayaran terbaru.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Keluhan Fasilitas -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100 rounded-3">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-secondary mb-0">Keluhan Fasilitas Terbaru</h5>
                <span class="badge bg-light text-muted border">Monitoring</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small">
                        <tr>
                            <th>Penghuni</th>
                            <th>Kamar</th>
                            <th>Judul</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @forelse($recentKeluhans as $keluhan)
                        <tr>
                            <td>{{ $keluhan->user->name ?? '-' }}</td>
                            <td>Kamar {{ $keluhan->kamar->nomor_kamar ?? '-' }}</td>
                            <td class="text-truncate" style="max-width: 150px;">{{ $keluhan->judul }}</td>
                            <td>
                                @if($keluhan->status === 'Selesai')
                                    <span class="badge bg-success">Selesai</span>
                                @elseif($keluhan->status === 'Diproses')
                                    <span class="badge bg-info text-dark">Diproses</span>
                                @else
                                    <span class="badge bg-warning text-dark">Menunggu</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">Tidak ada keluhan saat ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Rating & Ulasan Terbaru -->
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm h-100 rounded-3">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-secondary mb-0">Ulasan & Rating Penghuni</h5>
                <span class="badge bg-light text-muted border">Monitoring</span>
            </div>
            <div class="card-body p-3">
                @forelse($recentRatings as $rating)
                <div class="border-bottom pb-2 mb-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="fw-semibold small text-secondary">{{ $rating->user->name ?? 'Anonim' }}</span>
                        <span class="text-warning small">
                            @for($i = 1; $i <= 5; $i++)
                                <i class="bi bi-star{{ $i <= $rating->skor ? '-fill' : '' }}"></i>
                            @endfor
                        </span>
                    </div>
                    <p class="small text-muted mb-0 mt-1">{{ $rating->ulasan ?: 'Tidak ada komentar.' }}</p>
                </div>
                @empty
                <p class="text-muted small text-center my-4">Belum ada rating masuk.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
