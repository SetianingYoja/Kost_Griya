@extends('layouts.dashboard')

@section('title', 'Validasi Pembayaran Masuk')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold text-secondary mb-1">Validasi Pembayaran & Bukti Transfer</h3>
        <p class="text-muted small mb-0">Verifikasi mutasi transfer untuk booking kamar awal, tagihan bulanan, dan DP perpanjangan</p>
    </div>
    <div class="btn-group">
        <a href="{{ route('pemilik.pembayaran.index') }}" class="btn btn-sm {{ empty($status) ? 'btn-primary-griya' : 'btn-light border' }}">Semua</a>
        <a href="{{ route('pemilik.pembayaran.index', ['status' => 'Menunggu Validasi']) }}" class="btn btn-sm {{ $status === 'Menunggu Validasi' ? 'btn-primary-griya' : 'btn-light border' }}">Perlu Validasi</a>
        <a href="{{ route('pemilik.pembayaran.index', ['status' => 'Lunas']) }}" class="btn btn-sm {{ $status === 'Lunas' ? 'btn-primary-griya' : 'btn-light border' }}">Lunas</a>
        <a href="{{ route('pemilik.pembayaran.index', ['status' => 'Ditolak']) }}" class="btn btn-sm {{ $status === 'Ditolak' ? 'btn-primary-griya' : 'btn-light border' }}">Ditolak</a>
    </div>
</div>

<div class="card-griya p-4 mb-4">
    <form method="GET" action="{{ route('pemilik.pembayaran.index') }}" class="row g-3 align-items-end">
        @foreach(request()->except(['from_date','to_date','page']) as $key => $value)
            @if($value !== null && $value !== '')
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endif
        @endforeach
        <div class="col-md-4">
            <label class="form-label small fw-semibold text-secondary">Dari Tanggal</label>
            <input type="date" name="from_date" class="form-control" value="{{ $fromDate ?? '' }}">
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-semibold text-secondary">Sampai Tanggal</label>
            <input type="date" name="to_date" class="form-control" value="{{ $toDate ?? '' }}">
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary-griya btn-sm flex-fill">Cari</button>
            <a href="{{ route('pemilik.pembayaran.index', request()->only('status','jenis')) }}" class="btn btn-outline-secondary btn-sm flex-fill">Reset</a>
        </div>
    </form>
</div>

<div class="card-griya p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>Kode</th>
                    <th>Penghuni</th>
                    <th>Jenis Pembayaran</th>
                    <th>Tgl Bayar</th>
                    <th>Nominal</th>
                    <th>Bank & Pengirim</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pembayarans as $byr)
                    <tr>
                        <td><strong>{{ $byr->kode_pembayaran }}</strong></td>
                        <td>
                            <strong>{{ $byr->user->name }}</strong>
                            <div class="text-muted small">{{ $byr->user->phone }}</div>
                        </td>
                        <td><span class="badge bg-light text-primary border">{{ $byr->jenis_pembayaran }}</span></td>
                        <td>{{ $byr->tanggal_bayar ? $byr->tanggal_bayar->format('d/m/Y') : '-' }}</td>
                        <td><strong class="text-primary">Rp {{ number_format($byr->nominal, 0, ',', '.') }}</strong></td>
                        <td>
                            <div>{{ $byr->bank_pengirim }}</div>
                            <small class="text-muted">a.n. {{ $byr->nama_pengirim }}</small>
                        </td>
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
                            <a href="{{ route('pemilik.pembayaran.show', $byr->id) }}" class="btn btn-sm btn-outline-griya py-1 px-2">
                                <i class="bi bi-shield-check"></i> Validasi
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">Belum ada transaksi pembayaran yang sesuai filter.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $pembayarans->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
