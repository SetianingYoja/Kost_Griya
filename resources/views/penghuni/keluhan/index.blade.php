@extends('layouts.dashboard')

@section('title', 'Laporan Keluhan Fasilitas')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-secondary mb-1">Keluhan & Aspirasi Fasilitas</h3>
        <p class="text-muted small mb-0">Laporkan kendala fasilitas kamar atau area kost untuk segera ditangani</p>
    </div>
    <a href="{{ route('penghuni.keluhan.create') }}" class="btn btn-primary-griya btn-sm">
        <i class="bi bi-plus-circle me-1"></i> Buat Keluhan Baru
    </a>
</div>

<div class="card-griya p-4 mb-4">
    <form method="GET" action="{{ route('penghuni.keluhan.index') }}" class="row g-3 align-items-end">
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
            <a href="{{ route('penghuni.keluhan.index') }}" class="btn btn-outline-secondary btn-sm flex-fill">Reset</a>
        </div>
    </form>
</div>

<div class="card-griya p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>Kode</th>
                    <th>Judul Laporan</th>
                    <th>Tanggal Lapor</th>
                    <th>Status</th>
                    <th>Tanggapan Pemilik</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($keluhans as $keluhan)
                    <tr>
                        <td><strong>{{ $keluhan->kode_keluhan }}</strong></td>
                        <td>
                            <strong>{{ $keluhan->judul }}</strong>
                            <div class="text-muted small">{{ Str::limit($keluhan->isi, 40) }}</div>
                        </td>
                        <td>{{ $keluhan->created_at->format('d M Y') }}</td>
                        <td>
                            @if($keluhan->status === 'Selesai')
                                <span class="badge bg-success">Selesai</span>
                            @elseif($keluhan->status === 'Diproses')
                                <span class="badge bg-info text-dark">Sedang Diproses</span>
                            @elseif($keluhan->status === 'Ditolak')
                                <span class="badge bg-danger">Ditolak</span>
                            @else
                                <span class="badge bg-warning text-dark">Menunggu Tanggapan</span>
                            @endif
                        </td>
                        <td>
                            @if($keluhan->tanggapan)
                                <span class="text-secondary small">{{ Str::limit($keluhan->tanggapan, 40) }}</span>
                            @else
                                <span class="text-muted small">Belum ada respon</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('penghuni.keluhan.show', $keluhan->id) }}" class="btn btn-sm btn-outline-griya">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                            @if($keluhan->status === 'Selesai' && !$keluhan->rating)
                                <a href="{{ route('penghuni.rating.create', ['keluhan_id' => $keluhan->id]) }}" class="btn btn-sm btn-primary-griya ms-1">
                                    <i class="bi bi-star"></i> Beri Rating
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Belum ada laporan keluhan yang dibuat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $keluhans->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
