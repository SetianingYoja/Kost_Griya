@extends('layouts.dashboard')

@section('title', 'Data Kamar Kost — Pemilik Kost')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h3 class="fw-bold text-secondary mb-0">Data Kamar Kost Griya Ayu</h3>
            <span class="badge bg-secondary-subtle text-secondary border px-2 py-1 small"><i class="bi bi-eye me-1"></i>Read-Only</span>
        </div>
        <p class="text-muted small mb-0">Informasi nomor kamar, tipe, tarif sewa, fasilitas, dan status keterisian saat ini</p>
    </div>
</div>

<!-- Filter Bar -->
<div class="card-griya p-3 mb-4">
    <form action="{{ route('pemilik-kost.kamar.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-4">
            <input type="text" name="q" class="form-control form-control-sm" placeholder="Cari nomor kamar..." value="{{ request('q') }}">
        </div>
        <div class="col-md-3">
            <select name="tipe" class="form-select form-select-sm">
                <option value="">Semua Tipe Kamar</option>
                @foreach($tipeKamars as $t)
                    <option value="{{ $t->id }}" {{ request('tipe') == $t->id ? 'selected' : '' }}>{{ $t->nama_tipe }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select form-select-sm">
                <option value="">Semua Status</option>
                <option value="Tersedia" {{ request('status') == 'Tersedia' ? 'selected' : '' }}>Tersedia</option>
                <option value="Sedang Diproses" {{ request('status') == 'Sedang Diproses' ? 'selected' : '' }}>Sedang Diproses</option>
                <option value="Terisi" {{ request('status') == 'Terisi' ? 'selected' : '' }}>Terisi</option>
                <option value="Tidak tersedia" {{ request('status') == 'Tidak tersedia' ? 'selected' : '' }}>Tidak Tersedia</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-sm btn-primary-griya flex-grow-1">Filter</button>
            <a href="{{ route('pemilik-kost.kamar.index') }}" class="btn btn-sm btn-light border"><i class="bi bi-arrow-counterclockwise"></i></a>
        </div>
    </form>
</div>

<div class="card-griya p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>Foto</th>
                    <th>Nomor Kamar</th>
                    <th>Tipe</th>
                    <th>Lantai</th>
                    <th>Tarif / Bulan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($kamars as $kamar)
                    <tr>
                        <td style="width: 70px;">
                            <img src="{{ $kamar->foto_url }}" alt="{{ $kamar->nomor_kamar }}" class="rounded" style="width: 54px; height: 40px; object-fit: cover;">
                        </td>
                        <td><strong>{{ $kamar->nomor_kamar }}</strong></td>
                        <td>{{ $kamar->tipeKamar->nama_tipe ?? '-' }}</td>
                        <td>Lantai {{ $kamar->lantai }}</td>
                        <td><strong>Rp {{ number_format($kamar->harga, 0, ',', '.') }}</strong></td>
                        <td>
                            @if($kamar->status === 'Tersedia')
                                <span class="badge bg-success">Tersedia</span>
                            @elseif($kamar->status === 'Sedang Diproses')
                                <span class="badge bg-warning text-dark">Sedang Diproses</span>
                            @elseif($kamar->status === 'Terisi')
                                <span class="badge bg-danger">Terisi</span>
                            @else
                                <span class="badge bg-secondary">Tidak Tersedia</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('pemilik-kost.kamar.show', $kamar->id) }}" class="btn btn-sm btn-outline-griya py-1 px-3" title="Detail Kamar">
                                <i class="bi bi-eye me-1"></i> Detail
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Belum ada data kamar kost.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $kamars->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
