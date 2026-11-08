@extends('layouts.dashboard')

@section('title', 'Laporan Operasional & Keuangan — Pemilik Kost')

@push('styles')
<style>
    @page {
        size: A4 landscape;
        margin: 12mm;
    }

    @media print {
        html,
        body {
            width: 100% !important;
            min-height: 0 !important;
            background: #fff !important;
        }

        body {
            display: block !important;
            color: #17212b !important;
            font-family: Arial, sans-serif !important;
        }

        body > header.navbar,
        #dashboardSidebar,
        main > .alert {
            display: none !important;
        }

        .container-fluid {
            width: 100% !important;
            max-width: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .container-fluid > .row {
            display: block !important;
            margin: 0 !important;
        }

        main {
            display: block !important;
            width: 100% !important;
            max-width: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .report-print-header.d-print-block {
            display: flex !important;
        }

        .report-print-header {
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 14px;
            padding-bottom: 10px;
            border-bottom: 2px solid #172554;
        }

        .report-print-brand {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .report-print-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 8px;
            background: #e9eef3 !important;
            color: #172554 !important;
            font-size: 16pt;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .report-print-brand strong {
            display: block;
            color: #172554;
            font-size: 13pt;
            line-height: 1.1;
        }

        .report-print-brand span {
            display: block;
            color: #526071;
            font-size: 7.5pt;
            font-weight: 700;
            letter-spacing: 0.08em;
        }

        .report-print-heading {
            text-align: right;
        }

        .report-print-heading h1 {
            margin: 0;
            color: #172554;
            font-size: 14pt;
            font-weight: 700;
        }

        .report-print-heading p {
            margin: 2px 0 0;
            color: #526071;
            font-size: 8pt;
        }

        .report-print-period {
            margin-top: 4px !important;
            color: #17212b;
            font-size: 9pt;
            font-weight: 700;
        }

        #report-transactions {
            margin: 0 !important;
            padding: 0 !important;
            overflow: visible !important;
            border: 0 !important;
            border-radius: 0 !important;
            background: #fff !important;
            box-shadow: none !important;
            break-inside: auto;
        }

        #report-transactions h5 {
            margin-bottom: 6px !important;
            color: #172554 !important;
            font-size: 11pt;
        }

        #report-transactions .table-responsive {
            overflow: visible !important;
        }

        #report-transactions table {
            width: 100% !important;
            min-width: 0 !important;
            table-layout: fixed;
            border-collapse: collapse !important;
            font-size: 7.5pt !important;
        }

        #report-transactions thead {
            display: table-header-group;
        }

        #report-transactions tfoot {
            display: table-row-group;
        }

        #report-transactions th,
        #report-transactions td {
            padding: 4px 5px !important;
            color: #17212b !important;
            overflow-wrap: anywhere;
            vertical-align: top;
        }

        #report-transactions thead th {
            background: #e9eef3 !important;
            color: #172554 !important;
            font-weight: 700;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        #report-transactions th:nth-child(1),
        #report-transactions td:nth-child(1) { width: 16%; }
        #report-transactions th:nth-child(2),
        #report-transactions td:nth-child(2) { width: 11%; }
        #report-transactions th:nth-child(3),
        #report-transactions td:nth-child(3) { width: 18%; }
        #report-transactions th:nth-child(4),
        #report-transactions td:nth-child(4) { width: 10%; }
        #report-transactions th:nth-child(5),
        #report-transactions td:nth-child(5) { width: 17%; }
        #report-transactions th:nth-child(6),
        #report-transactions td:nth-child(6) { width: 13%; }
        #report-transactions th:nth-child(7),
        #report-transactions td:nth-child(7) { width: 15%; text-align: right; }

        #report-transactions tbody tr {
            break-inside: avoid;
            page-break-inside: avoid;
        }

        #report-transactions tfoot tr {
            border-top: 2px solid #172554;
            font-size: 8pt;
        }

        #report-transactions tfoot td {
            padding-top: 7px !important;
        }
    }
</style>
@endpush

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3 d-print-none">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h3 class="fw-bold text-secondary mb-0">Laporan Rekapitulasi Operasional & Keuangan</h3>
            <span class="badge bg-secondary-subtle text-secondary border px-2 py-1 small"><i class="bi bi-eye me-1"></i>Read-Only</span>
        </div>
        <p class="text-muted small mb-0">Rincian transaksi pembayaran lunas, okupansi kamar, dan evaluasi kepuasan penghuni</p>
    </div>
    <div class="d-flex gap-2">
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-printer me-1"></i> Cetak / Ekspor PDF
        </button>
    </div>
</div>

<header class="report-print-header d-none d-print-block">
    <div class="report-print-brand">
        <span class="report-print-icon"><i class="bi bi-buildings"></i></span>
        <div>
            <strong>Kost Putri Griya Ayu</strong>
            <span>MANAGEMENT PORTAL</span>
        </div>
    </div>
    <div class="report-print-heading">
        <h1>Laporan Rekapitulasi Operasional & Keuangan</h1>
        <p>Rincian transaksi pembayaran lunas dan okupansi</p>
        <p class="report-print-period">Periode: {{ Carbon\Carbon::parse($tanggalDari)->translatedFormat('d M Y') }} s/d {{ Carbon\Carbon::parse($tanggalSampai)->translatedFormat('d M Y') }}</p>
    </div>
</header>

<!-- Ringkasan Statistik Periode -->
<div class="row g-3 mb-4 d-print-none">
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div>
                <span class="small text-muted fw-medium d-block mb-1">Total Penerimaan Periode Ini</span>
                <h4 class="fw-bold text-success mb-0">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</h4>
                <small class="text-muted">{{ count($pembayarans) }} transaksi lunas</small>
            </div>
            <div class="stat-icon" style="background: rgba(16, 185, 129, 0.1); color: var(--brand-success);">
                <i class="bi bi-cash-stack"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div>
                <span class="small text-muted fw-medium d-block mb-1">Tingkat Okupansi Kamar</span>
                <h4 class="fw-bold text-primary mb-0">{{ $okupansiPersen }}%</h4>
                <small class="text-muted">{{ $kamarTerisi }} terisi dari {{ $totalKamar }} kamar</small>
            </div>
            <div class="stat-icon" style="background: rgba(37, 99, 235, 0.1); color: var(--brand-primary);">
                <i class="bi bi-pie-chart"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div>
                <span class="small text-muted fw-medium d-block mb-1">Keluhan Terselesaikan</span>
                <h4 class="fw-bold text-secondary mb-0">{{ $keluhanSelesaiBulanIni }} / {{ $totalKeluhanBulanIni }}</h4>
                <small class="text-muted">Periode terpilih</small>
            </div>
            <div class="stat-icon" style="background: rgba(245, 158, 11, 0.1); color: var(--brand-warning);">
                <i class="bi bi-check2-circle"></i>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div>
                <span class="small text-muted fw-medium d-block mb-1">Rata-rata Rating Kost</span>
                <h4 class="fw-bold text-warning mb-0">{{ number_format($avgRatingKost, 1) }} / 5.0</h4>
                <small class="text-muted">Kepuasan penghuni</small>
            </div>
            <div class="stat-icon" style="background: rgba(234, 179, 8, 0.1); color: #EAB308;">
                <i class="bi bi-star-fill"></i>
            </div>
        </div>
    </div>
</div>

<!-- Rincian Pemasukan / Transaksi -->
<div id="report-transactions" class="card-griya p-4 mb-4">
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3">
        <h5 class="fw-bold text-secondary mb-0"><i class="bi bi-journal-check text-primary me-2"></i>Rincian Transaksi Masuk Lunas</h5>
        <div class="d-flex align-items-center gap-2 d-print-none">
            <button type="submit" form="periodeFilterForm" class="btn btn-sm btn-primary-griya">
                <i class="bi bi-funnel me-1"></i>Terapkan Periode
            </button>
            @if(request('tanggal_dari') || request('tanggal_sampai'))
                <a href="{{ route('pemilik-kost.laporan.index') }}" class="btn btn-sm btn-outline-secondary" title="Kembalikan ke bulan berjalan">
                    <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                </a>
            @endif
        </div>
    </div>

    <form id="periodeFilterForm" action="{{ route('pemilik-kost.laporan.index') }}" method="GET" class="mb-3 d-print-none">
        <div class="row g-2">
            <div class="col-12 col-sm-6 col-xl-2">
                <label for="filterNomorTransaksi" class="form-label small mb-1">No. Transaksi</label>
                <input id="filterNomorTransaksi" type="search" class="form-control form-control-sm" placeholder="Cari nomor transaksi" data-transaction-filter="transaction">
            </div>
            <div class="col-12 col-sm-6 col-xl-2">
                <label for="filterPenghuni" class="form-label small mb-1">Nama / Penghuni</label>
                <input id="filterPenghuni" type="search" class="form-control form-control-sm" placeholder="Cari penghuni" data-transaction-filter="occupant">
            </div>
            <div class="col-12 col-sm-6 col-xl-2">
                <label for="filterKamar" class="form-label small mb-1">Nomor Kamar</label>
                <input id="filterKamar" type="search" class="form-control form-control-sm" placeholder="Cari nomor kamar" data-transaction-filter="room">
            </div>
            <div class="col-12 col-sm-6 col-xl-2">
                <label for="filterJenisPembayaran" class="form-label small mb-1">Jenis Pembayaran</label>
                <select id="filterJenisPembayaran" class="form-select form-select-sm" data-transaction-filter="type">
                    <option value="">Semua Jenis</option>
                    <option value="Booking Awal">Booking Awal</option>
                    <option value="DP Perpanjangan">DP Perpanjangan</option>
                    <option value="Pelunasan Perpanjangan">Pelunasan Perpanjangan</option>
                    <option value="Lunas Perpanjangan">Lunas Perpanjangan</option>
                    <option value="Tagihan Bulanan">Tagihan Bulanan</option>
                </select>
            </div>
            <div class="col-12 col-sm-6 col-xl-2">
                <label for="filterTanggalDari" class="form-label small mb-1">Tanggal Bayar Dari</label>
                <input id="filterTanggalDari" name="tanggal_dari" type="date" class="form-control form-control-sm" value="{{ $tanggalDari }}" data-transaction-filter="paidDate">
            </div>
            <div class="col-12 col-sm-6 col-xl-2">
                <label for="filterTanggalSampai" class="form-label small mb-1">Sampai Tanggal</label>
                <input id="filterTanggalSampai" name="tanggal_sampai" type="date" class="form-control form-control-sm" value="{{ $tanggalSampai }}" data-transaction-filter="paidDate">
            </div>
        </div>
    </form>
    <div class="table-responsive">
        <table class="table table-bordered align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>No. Transaksi</th>
                    <th>Tanggal Bayar</th>
                    <th>Penghuni</th>
                    <th>Kamar</th>
                    <th>Periode Sewa</th>
                    <th>Jenis</th>
                    <th class="text-end">Nominal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pembayarans as $bayar)
                    <tr data-transaction-row data-amount="{{ $bayar->nominal }}" data-transaction="{{ $bayar->kode_pembayaran }}" data-occupant="{{ $bayar->user->name }}" data-room="{{ $bayar->booking?->kamar?->nomor_kamar ?? $bayar->perpanjangan?->sewa?->kamar?->nomor_kamar ?? $bayar->tagihan?->sewa?->kamar?->nomor_kamar ?? '' }}" data-type="{{ $bayar->jenis_pembayaran }}" data-paid-date="{{ $bayar->tanggal_bayar?->format('Y-m-d') }}">
                        <td><strong>{{ $bayar->kode_pembayaran }}</strong></td>
                        <td>{{ $bayar->tanggal_bayar ? $bayar->tanggal_bayar->format('d/m/Y') : '-' }}</td>
                        <td>{{ $bayar->user->name }}</td>
                        <td>
                            @php
                                $nomorKamar = $bayar->booking?->kamar?->nomor_kamar
                                    ?? $bayar->perpanjangan?->sewa?->kamar?->nomor_kamar
                                    ?? $bayar->tagihan?->sewa?->kamar?->nomor_kamar;
                            @endphp
                            {{ $nomorKamar ? 'Kamar ' . $nomorKamar : '-' }}
                        </td>
                        <td>{{ $bayar->periode_sewa }}</td>
                        <td><span class="badge bg-light text-primary border">{{ $bayar->jenis_pembayaran }}</span></td>
                        <td class="text-end fw-bold">Rp {{ number_format($bayar->nominal, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Tidak ada transaksi pembayaran lunas pada periode ini.</td>
                    </tr>
                @endforelse
                <tr id="transactionFilterEmpty" class="d-none">
                    <td colspan="7" class="text-center py-4 text-muted">Tidak ada transaksi yang sesuai dengan filter.</td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="table-light fw-bold">
                    <td colspan="6" class="text-end">
                        <span class="d-print-none">Total Penerimaan:</span>
                        <span class="d-none d-print-inline">Total Pendapatan Terverifikasi:</span>
                    </td>
                    <td class="text-end text-primary fs-6">
                        <span class="d-print-none">Rp <span data-filtered-total>{{ number_format($totalPendapatan, 0, ',', '.') }}</span></span>
                        <span class="d-none d-print-inline">Rp <span data-filtered-total>{{ number_format($totalPendapatan, 0, ',', '.') }}</span></span>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
<script>
    const transactionFilters = document.querySelectorAll('[data-transaction-filter]');
    const transactionRows = document.querySelectorAll('[data-transaction-row]');
    const transactionFilterEmpty = document.getElementById('transactionFilterEmpty');
    const filteredTotals = document.querySelectorAll('[data-filtered-total]');
    const filterTanggalDari = document.getElementById('filterTanggalDari');
    const filterTanggalSampai = document.getElementById('filterTanggalSampai');
    const filterForm = document.getElementById('periodeFilterForm');

    if (filterForm) {
        filterForm.querySelectorAll('input:not([type="date"])').forEach((input) => {
            input.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                }
            });
        });
    }

    const filterTransactions = () => {
        let visibleRows = 0;
        let visibleAmount = 0;

        transactionRows.forEach((row) => {
            const matches = Array.from(transactionFilters).every((filter) => {
                if (filter.type === 'date') {
                    return true;
                }

                const term = filter.value.trim().toLowerCase();
                const value = row.dataset[filter.dataset.transactionFilter] || '';

                return !term || value.toLowerCase().includes(term);
            }) && (!filterTanggalDari.value || row.dataset.paidDate >= filterTanggalDari.value)
                && (!filterTanggalSampai.value || row.dataset.paidDate <= filterTanggalSampai.value);

            row.classList.toggle('d-none', !matches);
            if (matches) {
                visibleRows++;
                visibleAmount += Number(row.dataset.amount) || 0;
            }
        });

        transactionFilterEmpty.classList.toggle('d-none', visibleRows > 0 || transactionRows.length === 0);

        const formattedTotal = new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(visibleAmount);
        filteredTotals.forEach((total) => {
            total.textContent = formattedTotal;
        });
    };

    transactionFilters.forEach((filter) => {
        const eventName = filter.tagName === 'SELECT' || filter.type === 'date' ? 'change' : 'input';
        filter.addEventListener(eventName, filterTransactions);
    });

    filterTransactions();
</script>
@endsection
