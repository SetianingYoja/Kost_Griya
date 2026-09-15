@extends('layouts.dashboard')

@section('title', 'Laporan Sistem')

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Laporan Sistem</h4>
            <p class="text-muted mb-0">
                Ringkasan aktivitas dan data sistem Kost Putri Griya Ayu.
            </p>
        </div>
    </div>

    {{-- Statistik --}}
    <div class="row g-3 mb-4">

        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">Total Pendapatan</div>
                    <h4 class="fw-bold mb-0">
                        Rp {{ number_format($totalPendapatanAllTime, 0, ',', '.') }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">Total Booking</div>
                    <h4 class="fw-bold mb-0">
                        {{ number_format($totalBookingAllTime) }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">Total User</div>
                    <h4 class="fw-bold mb-0">
                        {{ number_format($totalUsers) }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">Total Kamar</div>
                    <h4 class="fw-bold mb-0">
                        {{ number_format($totalKamars) }}
                    </h4>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="text-muted small mb-1">Total Keluhan</div>
                    <h4 class="fw-bold mb-0">
                        {{ number_format($totalKeluhans) }}
                    </h4>
                </div>
            </div>
        </div>

    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.laporan') }}" class="row g-3 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-secondary">Dari Tanggal</label>
                    <input type="date" name="from_date" class="form-control" value="{{ $fromDate ?? '' }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-secondary">Sampai Tanggal</label>
                    <input type="date" name="to_date" class="form-control" value="{{ $toDate ?? '' }}">
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm flex-fill">Cari</button>
                    <a href="{{ route('admin.laporan') }}" class="btn btn-outline-secondary btn-sm flex-fill">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Riwayat Aktivitas --}}
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="fw-bold mb-0">Riwayat Aktivitas Sistem</h5>
        </div>

        <div class="card-body p-0">

            @if($auditLogs->count() > 0)

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="px-3">No</th>
                                <th>Aktivitas</th>
                                <th>Deskripsi</th>
                                <th>User</th>
                                <th>Tipe</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>

                        <tbody>
                            @foreach($auditLogs as $index => $log)
                                <tr>
                                    <td class="px-3">
                                        {{ $auditLogs->firstItem() + $index }}
                                    </td>

                                    <td>
                                        <div class="fw-semibold">
                                            {{ $log->judul }}
                                        </div>
                                    </td>

                                    <td>
                                        <span class="text-muted">
                                            {{ $log->deskripsi ?? '-' }}
                                        </span>
                                    </td>

                                    <td>
                                        {{ $log->user->name ?? '-' }}
                                    </td>

                                    <td>
                                        @php
                                            $badgeClass = match($log->tipe) {
                                                'success' => 'bg-success',
                                                'warning' => 'bg-warning text-dark',
                                                'danger' => 'bg-danger',
                                                default => 'bg-info text-dark',
                                            };
                                        @endphp

                                        <span class="badge {{ $badgeClass }}">
                                            {{ ucfirst($log->tipe ?? 'info') }}
                                        </span>
                                    </td>

                                    <td>
                                        <small class="text-muted">
                                            {{ $log->created_at?->format('d/m/Y H:i') ?? '-' }}
                                        </small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-3">
                    {{ $auditLogs->links() }}
                </div>

            @else

                <div class="text-center py-5">
                    <i class="bi bi-clock-history fs-1 text-muted"></i>
                    <p class="text-muted mt-3 mb-0">
                        Belum ada aktivitas sistem.
                    </p>
                </div>

            @endif

        </div>
    </div>

</div>
@endsection