@extends('layouts.dashboard')

@section('title', 'Dashboard Penghuni')

@section('content')
<div class="mb-4">
    <h2 class="h3 fw-bold text-secondary mb-1">Halo, {{ $user->name }}!</h2>
    <p class="text-muted small mb-0">Selamat datang di portal layanan digital Kost Putri Griya Ayu</p>
</div>

<!-- ============================================== -->
<!-- STATE 1: BELUM BOOKING                         -->
<!-- ============================================== -->
@if($state === 'belum_booking')
    <div class="card-griya p-4 p-md-5 text-center mb-4 border-primary">
        <div class="stat-icon mx-auto mb-3" style="background: rgba(37, 99, 235, 0.1); color: var(--brand-primary); width: 64px; height: 64px;">
            <i class="bi bi-door-open fs-2"></i>
        </div>
        <h3 class="h4 fw-bold text-secondary mb-2">Mulai Pengalaman Tinggal Nyaman Anda</h3>
        <p class="text-muted max-w-700 mx-auto mb-4" style="max-width: 600px;">
            Anda belum membooking kamar. Silakan pesan kamar sekarang untuk memulai masa sewa Anda di Kost Putri Griya Ayu.
        </p>
        <div>
            <a href="{{ route('kamar.index') }}" class="btn btn-primary-griya btn-lg px-4 py-2 fw-semibold">
                <i class="bi bi-calendar-check me-2"></i> Pesan Kamar Sekarang
            </a>
        </div>
    </div>

<!-- ============================================== -->
<!-- STATE 2: MENUNGGU VALIDASI BOOKING             -->
<!-- ============================================== -->
@elseif($state === 'menunggu_validasi_booking')
    <div class="card-griya p-4 mb-4 border-warning">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="d-flex align-items-start gap-3">
                <div class="stat-icon flex-shrink-0" style="background: rgba(245, 158, 11, 0.1); color: var(--brand-warning); width: 50px; height: 50px;">
                    <i class="bi bi-clock-history fs-4"></i>
                </div>
                <div>
                    <span class="badge bg-warning text-dark px-3 py-1 rounded-pill mb-1">Menunggu Validasi Pemilik</span>
                    <h4 class="fw-bold text-secondary mb-1">Pemesanan {{ $activeBooking->kamar->nomor_kamar ?? 'Kamar' }} Sedang Ditinjau</h4>
                    <p class="text-muted small mb-0">
                        Kode Booking: <strong>{{ $activeBooking->kode_booking }}</strong> | Tanggal Mulai: {{ $activeBooking->tanggal_mulai->format('d M Y') }} ({{ $activeBooking->durasi_bulan }} Bulan)
                    </p>
                </div>
            </div>
            <div>
                <a href="{{ route('penghuni.booking.show', $activeBooking->id) }}" class="btn btn-outline-griya btn-sm">
                    <i class="bi bi-eye me-1"></i> Detail Booking
                </a>
            </div>
        </div>
    </div>

<!-- ============================================== -->
<!-- STATE 3: MENUNGGU PEMBAYARAN                   -->
<!-- ============================================== -->
@elseif($state === 'menunggu_pembayaran')
    <div class="card-griya p-4 mb-4 border-primary">
        <div class="row g-4 align-items-center">
            <div class="col-lg-7">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-success px-3 py-1 rounded-pill">Booking Disetujui!</span>
                    <span class="badge bg-warning text-dark px-3 py-1 rounded-pill">Menunggu Pembayaran</span>
                </div>
                <h4 class="fw-bold text-secondary mb-2">Segera Selesaikan Pembayaran Sewa Kamar</h4>
                <p class="text-muted small mb-3">
                    Booking Anda untuk <strong>{{ $activeBooking->kamar->nomor_kamar }}</strong> telah disetujui. Silakan lakukan transfer sebesar nominal berikut sebelum batas waktu berakhir.
                </p>
                <div class="p-3 bg-light rounded-3 d-inline-block mb-3 border">
                    <span class="small text-muted d-block">Total Nominal yang Harus Ditransfer:</span>
                    <span class="fs-3 fw-bold text-primary">Rp {{ number_format($activeBooking->total_harga, 0, ',', '.') }}</span>
                </div>
                <div class="small text-muted">
                    <i class="bi bi-clock text-danger me-1"></i>
                    Batas Pembayaran: <strong>{{ $activeBooking->batas_pembayaran ? $activeBooking->batas_pembayaran->format('d M Y, H:i') . ' WIB' : '24 Jam' }}</strong>
                </div>
            </div>
            <div class="col-lg-5">
                <div class="p-3 bg-light rounded-3 border">
                    <strong class="d-block text-secondary small mb-2"><i class="bi bi-bank me-1"></i> Rekening Resmi Kost Griya Ayu:</strong>
                    <div class="fw-bold fs-5 text-secondary">{{ $kostInfo->bank_nama ?? 'Bank BCA' }}</div>
                    <div class="fs-4 fw-bold text-primary letter-spacing-1">{{ $kostInfo->bank_rekening ?? '8415291039' }}</div>
                    <div class="small text-muted mb-3">a.n. {{ $kostInfo->bank_atas_nama ?? 'Kost Putri Griya Ayu' }}</div>
                    <a href="{{ route('penghuni.pembayaran.create', ['booking_id' => $activeBooking->id]) }}" class="btn btn-primary-griya w-100 fw-semibold">
                        <i class="bi bi-upload me-2"></i> Unggah Bukti Pembayaran
                    </a>
                </div>
            </div>
        </div>
    </div>

<!-- ============================================== -->
<!-- STATE 4: AKTIF MENYEWA                         -->
<!-- ============================================== -->
@elseif($state === 'aktif_menyewa')
    <div class="row g-4 mb-4">
        <!-- Info Kamar & Sewa Aktif -->
        <div class="col-lg-8">
            <div class="card-griya p-4 h-100">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
                    <div>
                        <span class="badge bg-success px-3 py-1 rounded-pill mb-1"><i class="bi bi-check-circle-fill me-1"></i> Aktif Menyewa</span>
                        <h3 class="h4 fw-bold text-secondary mb-0">{{ $activeSewa->kamar->nomor_kamar ?? 'Kamar Kost' }} ({{ $activeSewa->kamar->tipeKamar->nama_tipe ?? 'Tipe' }})</h3>
                    </div>
                    <div class="text-md-end mt-2 mt-md-0">
                        <span class="small text-muted d-block">Sisa Durasi Sewa:</span>
                        <span class="fs-4 fw-bold text-primary">{{ $activeSewa->sisa_hari }} Hari Lagi</span>
                    </div>
                </div>

                <div class="row g-3 py-3 my-2 border-top border-bottom text-secondary small">
                    <div class="col-sm-4">
                        <span class="text-muted d-block">Mulai Sewa:</span>
                        <strong>{{ $activeSewa->tanggal_mulai->format('d M Y') }}</strong>
                    </div>
                    <div class="col-sm-4">
                        <span class="text-muted d-block">Berakhir Pada:</span>
                        <strong class="text-danger">{{ $activeSewa->tanggal_selesai->format('d M Y') }}</strong>
                    </div>
                    <div class="col-sm-4">
                        <span class="text-muted d-block">Tarif Sewa:</span>
                        <strong>Rp {{ number_format($activeSewa->harga_per_bulan, 0, ',', '.') }} / bln</strong>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2 pt-2 mt-auto">
                    <a href="{{ route('penghuni.perpanjangan.create') }}" class="btn btn-primary-griya btn-sm">
                        <i class="bi bi-arrow-repeat me-1"></i> Ajukan Perpanjangan Sewa
                    </a>
                    <a href="{{ route('penghuni.keluhan.create') }}" class="btn btn-outline-griya btn-sm">
                        <i class="bi bi-exclamation-octagon me-1"></i> Laporkan Keluhan Fasilitas
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Status Tagihan -->
        <div class="col-lg-4">
            <div class="card-griya p-4 h-100">
                <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-receipt text-primary me-2"></i>Status Tagihan</h5>
                @if($unpaidTagihans->count() > 0)
                    <div class="alert alert-warning small mb-3">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Anda memiliki <strong>{{ $unpaidTagihans->count() }}</strong> tagihan yang perlu diselesaikan.
                    </div>
                    @foreach($unpaidTagihans as $tagihan)
                        <div class="p-3 bg-light rounded-3 border mb-2">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <strong class="text-secondary small">{{ $tagihan->periode }}</strong>
                                <span class="badge bg-danger">{{ $tagihan->status }}</span>
                            </div>
                            <div class="fs-5 fw-bold text-primary mb-2">Rp {{ number_format($tagihan->total_bayar, 0, ',', '.') }}</div>
                            <a href="{{ route('penghuni.pembayaran.create', ['tagihan_id' => $tagihan->id]) }}" class="btn btn-sm btn-primary-griya w-100">
                                <i class="bi bi-upload me-1"></i> Bayar Tagihan
                            </a>
                        </div>
                    @endforeach
                @else
                    <div class="text-center py-4">
                        <div class="stat-icon mx-auto mb-2" style="background: rgba(16, 185, 129, 0.1); color: var(--brand-success); width: 44px; height: 44px;">
                            <i class="bi bi-check2-all fs-4"></i>
                        </div>
                        <h6 class="fw-bold text-secondary mb-1">Seluruh Tagihan Lunas</h6>
                        <p class="text-muted small mb-0">Tidak ada tagihan tertunggak untuk kamar Anda.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

<!-- ============================================== -->
<!-- STATE 5: MASA SEWA BERAKHIR                    -->
<!-- ============================================== -->
@elseif($state === 'masa_sewa_berakhir')
    <div class="card-griya p-4 p-md-5 text-center mb-4 border-secondary">
        <div class="stat-icon mx-auto mb-3" style="background: rgba(100, 116, 139, 0.1); color: #64748B; width: 64px; height: 64px;">
            <i class="bi bi-calendar-x fs-2"></i>
        </div>
        <h3 class="h4 fw-bold text-secondary mb-2">Masa Sewa Anda Telah Selesai</h3>
        <p class="text-muted max-w-700 mx-auto mb-4" style="max-width: 600px;">
            Terima kasih telah menjadi bagian dari keluarga Kost Putri Griya Ayu. Seluruh arsip transaksi, tagihan, dan riwayat Anda tetap tersimpan aman di akun ini. Anda dapat memesan kamar kembali kapan pun Anda membutuhkan.
        </p>
        <div class="d-flex justify-content-center gap-2">
            <a href="{{ route('kamar.index') }}" class="btn btn-primary-griya px-4">
                <i class="bi bi-door-open me-2"></i> Pesan Kamar Baru
            </a>
            <a href="{{ route('penghuni.riwayat.index') }}" class="btn btn-outline-griya px-4">
                <i class="bi bi-clock-history me-2"></i> Lihat Riwayat Sewa
            </a>
        </div>
    </div>
@endif

<!-- Notifikasi Rating Keluhan yang Selesai -->
@if($pendingKeluhanForRating)
    <div class="alert alert-info border-0 shadow-sm rounded-3 p-4 mb-4 d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div class="d-flex align-items-start gap-3">
            <i class="bi bi-star-fill text-warning fs-3"></i>
            <div>
                <h5 class="fw-bold mb-1">Keluhan Anda Telah Selesai Ditangani!</h5>
                <p class="mb-0 small text-secondary">
                    Laporan: "<strong>{{ $pendingKeluhanForRating->judul }}</strong>" telah diselesaikan oleh Pemilik Kost. Mohon berikan penilaian atas pelayanan kami.
                </p>
            </div>
        </div>
        <a href="{{ route('penghuni.rating.create', ['keluhan_id' => $pendingKeluhanForRating->id]) }}" class="btn btn-primary-griya btn-sm flex-shrink-0">
            <i class="bi bi-star me-1"></i> Beri Penilaian Sekarang
        </a>
    </div>
@endif

<!-- Recent Activity Tables -->
<div class="row g-4">
    <!-- Riwayat Pembayaran Terakhir -->
    <div class="col-lg-6">
        <div class="card-griya p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-secondary mb-0"><i class="bi bi-credit-card text-primary me-2"></i>Pembayaran Terakhir</h5>
                <a href="{{ route('penghuni.pembayaran.index') }}" class="small text-primary text-decoration-none">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kode</th>
                            <th>Jenis</th>
                            <th>Nominal</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pembayarans as $bayar)
                            <tr>
                                <td><strong>{{ $bayar->kode_pembayaran }}</strong></td>
                                <td>{{ $bayar->jenis_pembayaran }}</td>
                                <td>Rp {{ number_format($bayar->nominal, 0, ',', '.') }}</td>
                                <td>
                                    @if($bayar->status === 'Lunas')
                                        <span class="badge bg-success">Lunas</span>
                                    @elseif($bayar->status === 'Ditolak')
                                        <span class="badge bg-danger">Ditolak</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Validasi</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">Belum ada transaksi pembayaran.</td>
                            </tr>
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
                <h5 class="fw-bold text-secondary mb-0"><i class="bi bi-exclamation-octagon text-primary me-2"></i>Laporan Keluhan</h5>
                <a href="{{ route('penghuni.keluhan.index') }}" class="small text-primary text-decoration-none">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Judul Keluhan</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($keluhans as $klh)
                            <tr>
                                <td>
                                    <strong>{{ $klh->judul }}</strong>
                                    <div class="text-muted small">{{ Str::limit($klh->isi, 35) }}</div>
                                </td>
                                <td>{{ $klh->created_at->format('d/m/Y') }}</td>
                                <td>
                                    @if($klh->status === 'Selesai')
                                        <span class="badge bg-success">Selesai</span>
                                    @elseif($klh->status === 'Diproses')
                                        <span class="badge bg-info text-dark">Diproses</span>
                                    @elseif($klh->status === 'Ditolak')
                                        <span class="badge bg-danger">Ditolak</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Menunggu</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center text-muted py-3">Tidak ada keluhan fasilitas.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
