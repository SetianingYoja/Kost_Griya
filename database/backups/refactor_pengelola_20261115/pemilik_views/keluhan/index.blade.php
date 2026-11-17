@extends('layouts.dashboard')

@section('title', 'Kelola Keluhan Penghuni')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold text-secondary mb-1">Daftar Keluhan Fasilitas</h3>
        <p class="text-muted small mb-0">Tindaklanjuti keluhan dan laporan kerusakan fasilitas dari para penghuni kost</p>
    </div>
    <div class="btn-group">
        <a href="{{ route('pemilik.keluhan.index') }}" class="btn btn-sm {{ empty($status) ? 'btn-primary-griya' : 'btn-light border' }}">Semua</a>
        <a href="{{ route('pemilik.keluhan.index', ['status' => 'Menunggu']) }}" class="btn btn-sm {{ $status === 'Menunggu' ? 'btn-primary-griya' : 'btn-light border' }}">Menunggu</a>
        <a href="{{ route('pemilik.keluhan.index', ['status' => 'Diproses']) }}" class="btn btn-sm {{ $status === 'Diproses' ? 'btn-primary-griya' : 'btn-light border' }}">Diproses</a>
        <a href="{{ route('pemilik.keluhan.index', ['status' => 'Selesai']) }}" class="btn btn-sm {{ $status === 'Selesai' ? 'btn-primary-griya' : 'btn-light border' }}">Selesai</a>
    </div>
</div>

<div class="card-griya p-4 mb-4">
    <form method="GET" action="{{ route('pemilik.keluhan.index') }}" class="row g-3 align-items-end">
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
            <a href="{{ route('pemilik.keluhan.index', request()->only('status')) }}" class="btn btn-outline-secondary btn-sm flex-fill">Reset</a>
        </div>
    </form>
</div>

<div class="card-griya p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>Kode</th>
                    <th>Penghuni & Kamar</th>
                    <th>Judul Keluhan</th>
                    <th>Tanggal Lapor</th>
                    <th>Status</th>
                    <th>Tanggapan Terakhir</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($keluhans as $klh)
                    <tr>
                        <td><strong>{{ $klh->kode_keluhan }}</strong></td>
                        <td>
                            <strong>{{ $klh->user->name }}</strong>
                            <div class="text-muted small">Kamar: {{ $klh->kamar->nomor_kamar ?? 'Area Umum' }}</div>
                        </td>
                        <td>
                            <strong>{{ $klh->judul }}</strong>
                            <div class="text-muted small">{{ Str::limit($klh->isi, 35) }}</div>
                        </td>
                        <td>{{ $klh->created_at->format('d/m/Y H:i') }}</td>
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
                        <td>{{ Str::limit($klh->tanggapan ?: 'Belum ditanggapi', 30) }}</td>
                        <td>
                            <a href="{{ route('pemilik.keluhan.show', $klh->id) }}" class="btn btn-sm btn-outline-griya py-1 px-2">
                                <i class="bi bi-chat-dots"></i> Tanggapi
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Belum ada keluhan yang sesuai filter.</td>
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
