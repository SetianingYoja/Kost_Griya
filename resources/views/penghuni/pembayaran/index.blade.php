@extends('layouts.dashboard')

@section('title', 'Riwayat Pembayaran Saya')

@section('content')
<div class="mb-4">
    <h3 class="fw-bold text-secondary mb-1">Daftar Transaksi Pembayaran</h3>
    <p class="text-muted small mb-0">Riwayat transfer dan verifikasi bukti pembayaran kost Anda</p>
</div>

<div class="card-griya p-4 mb-4">
    <form method="GET" action="{{ route('penghuni.pembayaran.index') }}" class="row g-3 align-items-end">
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
            <a href="{{ route('penghuni.pembayaran.index') }}" class="btn btn-outline-secondary btn-sm flex-fill">Reset</a>
        </div>
    </form>
</div>

<div class="card-griya p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>Kode Pembayaran</th>
                    <th>Jenis</th>
                    <th>Tanggal Transfer</th>
                    <th>Nominal</th>
                    <th>Bank & Pengirim</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pembayarans as $pembayaran)
                    <tr>
                        <td><strong>{{ $pembayaran->kode_pembayaran }}</strong></td>
                        <td>
                            <span class="badge bg-light text-primary border">{{ $pembayaran->jenis_pembayaran }}</span>
                        </td>
                        <td>{{ $pembayaran->tanggal_bayar ? $pembayaran->tanggal_bayar->format('d M Y') : '-' }}</td>
                        <td><strong>Rp {{ number_format($pembayaran->nominal, 0, ',', '.') }}</strong></td>
                        <td>
                            <div>{{ $pembayaran->bank_pengirim }}</div>
                            <small class="text-muted">a.n. {{ $pembayaran->nama_pengirim }}</small>
                        </td>
                        <td>
                            @if($pembayaran->status === 'Lunas')
                                <span class="badge bg-success">Lunas</span>
                            @elseif($pembayaran->status === 'Ditolak')
                                <span class="badge bg-danger">Ditolak</span>
                            @else
                                <span class="badge bg-warning text-dark">Menunggu Validasi</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('penghuni.pembayaran.show', $pembayaran->id) }}" class="btn btn-sm btn-outline-griya">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Belum ada transaksi pembayaran yang dilakukan.</td>
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
