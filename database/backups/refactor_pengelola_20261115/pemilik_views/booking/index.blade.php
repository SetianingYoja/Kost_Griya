@extends('layouts.dashboard')

@section('title', 'Validasi Booking Kamar')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold text-secondary mb-1">Validasi Pemesanan Kamar</h3>
        <p class="text-muted small mb-0">Tinjau dan setujui permohonan booking calon penghuni kost</p>
    </div>
    <div class="btn-group">
        <a href="{{ route('pemilik.booking.index') }}" class="btn btn-sm {{ empty($status) ? 'btn-primary-griya' : 'btn-light border' }}">Semua</a>
        <a href="{{ route('pemilik.booking.index', ['status' => 'Menunggu Validasi']) }}" class="btn btn-sm {{ $status === 'Menunggu Validasi' ? 'btn-primary-griya' : 'btn-light border' }}">Menunggu Validasi</a>
        <a href="{{ route('pemilik.booking.index', ['status' => 'Menunggu Pembayaran']) }}" class="btn btn-sm {{ $status === 'Menunggu Pembayaran' ? 'btn-primary-griya' : 'btn-light border' }}">Menunggu Bayar</a>
        <a href="{{ route('pemilik.booking.index', ['status' => 'Selesai']) }}" class="btn btn-sm {{ $status === 'Selesai' ? 'btn-primary-griya' : 'btn-light border' }}">Selesai</a>
    </div>
</div>

<div class="card-griya p-4 mb-4">
    <form method="GET" action="{{ route('pemilik.booking.index') }}" class="row g-3 align-items-end">
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
            <a href="{{ route('pemilik.booking.index', request()->only('status')) }}" class="btn btn-outline-secondary btn-sm flex-fill">Reset</a>
        </div>
    </form>
</div>

<div class="card-griya p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>Kode Booking</th>
                    <th>Calon Penghuni</th>
                    <th>Kamar</th>
                    <th>Tgl Mulai</th>
                    <th>Durasi</th>
                    <th>Total Biaya</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $bkg)
                    <tr>
                        <td><strong>{{ $bkg->kode_booking }}</strong></td>
                        <td>
                            <strong>{{ $bkg->user->name }}</strong>
                            <div class="text-muted small"><i class="bi bi-whatsapp text-success me-1"></i>{{ $bkg->user->phone }}</div>
                        </td>
                        <td>
                            <strong>{{ $bkg->kamar->nomor_kamar ?? '-' }}</strong>
                            <div class="text-muted small">{{ $bkg->kamar->tipeKamar->nama_tipe ?? '' }}</div>
                        </td>
                        <td>{{ $bkg->tanggal_mulai->format('d/m/Y') }}</td>
                        <td>{{ $bkg->durasi_bulan }} Bln</td>
                        <td><strong>Rp {{ number_format($bkg->total_harga, 0, ',', '.') }}</strong></td>
                        <td>
                            @if($bkg->status === 'Menunggu Validasi')
                                <span class="badge bg-warning text-dark">Menunggu Validasi</span>
                            @elseif($bkg->status === 'Menunggu Pembayaran')
                                <span class="badge bg-primary">Menunggu Bayar</span>
                            @elseif($bkg->status === 'Selesai')
                                <span class="badge bg-success">Selesai (Lunas)</span>
                            @elseif($bkg->status === 'Ditolak')
                                <span class="badge bg-danger">Ditolak</span>
                            @else
                                <span class="badge bg-secondary">{{ $bkg->status }}</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('pemilik.booking.show', $bkg->id) }}" class="btn btn-sm btn-outline-griya py-1 px-2">
                                <i class="bi bi-eye"></i> Periksa
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">Tidak ada permohonan booking yang sesuai.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $bookings->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
