@extends('layouts.dashboard')

@section('title', 'Laporan Operasional Kost')

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
            gap: 9px;
            min-width: 230px;
        }

        .report-print-icon {
            display: flex;
            width: 34px;
            height: 34px;
            align-items: center;
            justify-content: center;
            border: 1px solid #172554;
            color: #172554;
            font-size: 18px;
        }

        .report-print-brand strong,
        .report-print-brand span {
            display: block;
        }

        .report-print-brand strong {
            color: #172554;
            font-size: 12pt;
        }

        .report-print-brand span {
            margin-top: 2px;
            color: #526170;
            font-size: 7pt;
            font-weight: 700;
        }

        .report-print-heading {
            flex: 1;
            text-align: right;
        }

        .report-print-heading h1 {
            margin: 0;
            color: #172554;
            font-size: 17pt;
            font-weight: 700;
        }

        .report-print-heading p {
            margin: 2px 0 0;
            color: #526170;
            font-size: 8pt;
        }

        .report-print-heading .report-print-period {
            margin-top: 5px;
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
        #report-transactions td:nth-child(2) { width: 12%; }
        #report-transactions th:nth-child(3),
        #report-transactions td:nth-child(3) { width: 20%; }
        #report-transactions th:nth-child(4),
        #report-transactions td:nth-child(4) { width: 9%; }
        #report-transactions th:nth-child(5),
        #report-transactions td:nth-child(5) { width: 19%; }
        #report-transactions th:nth-child(6),
        #report-transactions td:nth-child(6) { width: 12%; }
        #report-transactions th:nth-child(7),
        #report-transactions td:nth-child(7) { width: 12%; }

        #report-transactions tbody tr,
        #report-transactions tfoot tr {
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
    <div class="d-print-none">
        <h3 class="fw-bold text-secondary mb-1">Laporan Rekapitulasi Operasional</h3>
        <p class="text-muted small mb-0">Rincian transaksi pembayaran lunas</p>
    </div>
    <div class="d-flex gap-2 d-print-none">
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
        <h1>Laporan Rekapitulasi Operasional</h1>
        <p>Rincian transaksi pembayaran lunas</p>
        <p class="report-print-period">Periode: {{ Carbon\Carbon::create($tahun, $bulan, 1)->translatedFormat('F Y') }}</p>
    </div>
</header>

<!-- Rincian Pemasukan / Transaksi -->
<div id="report-transactions" class="card-griya p-4 mb-4">
    <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-journal-check text-primary me-2"></i>Rincian Transaksi Masuk Lunas</h5>
    <div class="row g-2 mb-3 d-print-none">
        <div class="col-12 col-sm-6 col-xl-2">
            <label for="filterNomorTransaksi" class="form-label small mb-1">No. Transaksi</label>
            <input id="filterNomorTransaksi" type="search" class="form-control form-control-sm" placeholder="Cari nomor transaksi" data-transaction-filter="transaction">
        </div>
        <div class="col-12 col-sm-6 col-xl-2">
            <label for="filterPenghuni" class="form-label small mb-1">Nama / Penghuni</label>
            <input id="filterPenghuni" type="search" class="form-control form-control-sm" placeholder="Cari penghuni" data-transaction-filter="occupant">
        </div>
        <div class="col-12 col-sm-6 col-xl-2">
            <label for="filterKamar" class="form-label small mb-1">Kamar</label>
            <input id="filterKamar" type="search" class="form-control form-control-sm" placeholder="Cari kamar" data-transaction-filter="room">
        </div>
        <div class="col-12 col-sm-6 col-xl-2">
            <label for="filterJenis" class="form-label small mb-1">Jenis</label>
            <select id="filterJenis" class="form-select form-select-sm" data-transaction-filter="type">
                <option value="">Semua jenis</option>
                @foreach($pembayarans->pluck('jenis_pembayaran')->filter()->unique()->sort() as $jenis)
                    <option value="{{ $jenis }}">{{ $jenis }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-12 col-sm-6 col-xl-2">
            <label for="filterTanggalDari" class="form-label small mb-1">Dari Tanggal</label>
            <input id="filterTanggalDari" type="date" class="form-control form-control-sm" data-transaction-filter="paidDate">
        </div>
        <div class="col-12 col-sm-6 col-xl-2">
            <label for="filterTanggalSampai" class="form-label small mb-1">Sampai Tanggal</label>
            <input id="filterTanggalSampai" type="date" class="form-control form-control-sm" data-transaction-filter="paidDate">
        </div>
    </div>
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
