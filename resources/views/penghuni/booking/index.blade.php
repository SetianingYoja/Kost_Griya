@extends('layouts.dashboard')

@section('title', 'Pemesanan Kamar Saya')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-secondary mb-1">Riwayat Pemesanan Kamar</h3>
        <p class="text-muted small mb-0">Daftar permohonan booking kamar kost Griya Ayu Anda</p>
    </div>
</div>

<div class="card-griya p-4 mb-4">
    <form method="GET" action="{{ route('penghuni.booking.index') }}" class="row g-3 align-items-end">
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
            <a href="{{ route('penghuni.booking.index') }}" class="btn btn-outline-secondary btn-sm flex-fill">Reset</a>
        </div>
    </form>
</div>

<div class="card-griya p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>Kode Booking</th>
                    <th>Kamar</th>
                    <th>Tanggal Mulai</th>
                    <th>Durasi</th>
                    <th>Total Biaya</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($bookings as $booking)
                    <tr>
                        <td><strong>{{ $booking->kode_booking }}</strong></td>
                        <td>
                            <strong>{{ $booking->kamar->nomor_kamar ?? '-' }}</strong>
                            <div class="text-muted small">{{ $booking->kamar->tipeKamar->nama_tipe ?? '' }}</div>
                        </td>
                        <td>{{ $booking->tanggal_mulai->format('d M Y') }}</td>
                        <td>{{ $booking->durasi_bulan }} Bulan</td>
                        <td><strong>Rp {{ number_format($booking->total_harga, 0, ',', '.') }}</strong></td>
                        <td>
                            @if($booking->status === 'Menunggu Validasi')
                                <span class="badge bg-warning text-dark">Menunggu Validasi</span>
                            @elseif($booking->status === 'Menunggu Pembayaran' || $booking->status === 'Disetujui')
                                <span class="badge bg-primary">Menunggu Pembayaran</span>
                            @elseif($booking->status === 'Selesai')
                                <span class="badge bg-success">Selesai (Lunas)</span>
                            @elseif($booking->status === 'Ditolak')
                                <span class="badge bg-danger">Ditolak</span>
                            @else
                                <span class="badge bg-secondary">{{ $booking->status }}</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('penghuni.booking.show', $booking->id) }}" class="btn btn-sm btn-outline-griya">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                            @if(in_array($booking->status, ['Menunggu Pembayaran', 'Disetujui']))
                                <a href="{{ route('penghuni.pembayaran.create', ['booking_id' => $booking->id]) }}" class="btn btn-sm btn-primary-griya ms-1">
                                    <i class="bi bi-upload"></i> Bayar
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            Belum ada riwayat pemesanan kamar. <a href="{{ route('kamar.index') }}">Pesan kamar sekarang</a>.
                        </td>
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
