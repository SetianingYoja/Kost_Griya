@extends('layouts.dashboard')

@section('title', 'Laporan Operasional Kost')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold text-secondary mb-1">Laporan Rekapitulasi Operasional</h3>
        <p class="text-muted small mb-0">Rekapitulasi keuangan, okupansi kamar, dan performa penanganan keluhan</p>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-printer me-1"></i> Cetak / Ekspor PDF
        </button>
    </div>
</div>

<!-- Filter Bulan & Tahun -->
<div class="card-griya p-3 mb-4 d-print-none">
    <form action="{{ route('pemilik.laporan.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-4">
            <select name="bulan" class="form-select form-select-sm">
                @for($m = 1; $m <= 12; $m++)
                    @php $num = sprintf('%02d', $m); @endphp
                    <option value="{{ $num }}" {{ $bulan == $num ? 'selected' : '' }}>
                        {{ Carbon\Carbon::create(2026, $m, 1)->translatedFormat('F') }}
                    </option>
                @endfor
            </select>
        </div>
        <div class="col-md-4">
            <select name="tahun" class="form-select form-select-sm">
                @for($y = date('Y'); $y >= date('Y') - 3; $y--)
                    <option value="{{ $y }}" {{ $tahun == $y ? 'selected' : '' }}>Tahun {{ $y }}</option>
                @endfor
            </select>
        </div>
        <div class="col-md-4 d-flex gap-1">
            <button type="submit" class="btn btn-sm btn-primary-griya flex-grow-1">Tampilkan Laporan</button>
        </div>
    </form>
</div>

<!-- Ringkasan Kartu -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stat-card">
            <div>
                <span class="small text-muted fw-medium d-block mb-1">Total Pendapatan Terverifikasi</span>
                <h3 class="fw-bold text-primary mb-0">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</h3>
                <small class="text-muted">Periode {{ Carbon\Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y') }}</small>
            </div>
            <div class="stat-icon" style="background: rgba(37, 99, 235, 0.1); color: var(--brand-primary);">
                <i class="bi bi-cash-stack"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card">
            <div>
                <span class="small text-muted fw-medium d-block mb-1">Tingkat Okupansi Kamar</span>
                <h3 class="fw-bold text-secondary mb-0">{{ $okupansiPersen }}%</h3>
                <small class="text-muted">{{ $kamarTerisi }} Terisi dari {{ $totalKamar }} Unit</small>
            </div>
            <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--brand-success);">
                <i class="bi bi-pie-chart"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="stat-card">
            <div>
                <span class="small text-muted fw-medium d-block mb-1">Keluhan Selesai Ditangani</span>
                <h3 class="fw-bold text-secondary mb-0">{{ $keluhanSelesaiBulanIni }} / {{ $totalKeluhanBulanIni }}</h3>
                <small class="text-muted">Rating Kost: {{ number_format($avgRatingKost, 1) }} / 5.0</small>
            </div>
            <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: var(--brand-warning);">
                <i class="bi bi-check2-circle"></i>
            </div>
        </div>
    </div>
</div>

<!-- Rincian Pemasukan / Transaksi -->
<div class="card-griya p-4 mb-4">
    <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-journal-check text-primary me-2"></i>Rincian Transaksi Masuk Lunas</h5>
    <div class="table-responsive">
        <table class="table table-bordered align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>No. Transaksi</th>
                    <th>Tanggal Bayar</th>
                    <th>Penghuni</th>
                    <th>Kamar / Keterangan</th>
                    <th>Jenis</th>
                    <th class="text-end">Nominal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pembayarans as $bayar)
                    <tr>
                        <td><strong>{{ $bayar->kode_pembayaran }}</strong></td>
                        <td>{{ $bayar->tanggal_bayar ? $bayar->tanggal_bayar->format('d/m/Y') : '-' }}</td>
                        <td>{{ $bayar->user->name }}</td>
                        <td>
                            @if($bayar->booking)
                                Kamar {{ $bayar->booking->kamar->nomor_kamar ?? '' }} (Booking Awal)
                            @elseif($bayar->tagihan)
                                {{ $bayar->tagihan->periode }}
                            @elseif($bayar->perpanjangan)
                                DP Perpanjangan {{ $bayar->perpanjangan->durasi_bulan }} Bln
                            @else
                                Pembayaran Kost
                            @endif
                        </td>
                        <td><span class="badge bg-light text-primary border">{{ $bayar->jenis_pembayaran }}</span></td>
                        <td class="text-end fw-bold">Rp {{ number_format($bayar->nominal, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Tidak ada transaksi pembayaran lunas pada periode ini.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="table-light fw-bold">
                    <td colspan="5" class="text-end">Total Penerimaan:</td>
                    <td class="text-end text-primary fs-6">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
