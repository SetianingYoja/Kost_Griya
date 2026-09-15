@extends('layouts.dashboard')

@section('title', 'Manajemen Kamar Kost')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold text-secondary mb-1">Manajemen Data Kamar Kost</h3>
        <p class="text-muted small mb-0">Kelola nomor kamar, tipe kamar, tarif sewa, fasilitas, dan status ketersediaan</p>
    </div>
    <a href="{{ route('pemilik.kamar.create') }}" class="btn btn-primary-griya btn-sm">
        <i class="bi bi-plus-circle me-1"></i> Tambah Kamar Baru
    </a>
</div>

<!-- Filter Bar -->
<div class="card-griya p-3 mb-4">
    <form action="{{ route('pemilik.kamar.index') }}" method="GET" class="row g-2 align-items-center">
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
                <option value="Terisi" {{ request('status') == 'Terisi' ? 'selected' : '' }}>Terisi</option>
                <option value="Tidak tersedia" {{ request('status') == 'Tidak tersedia' ? 'selected' : '' }}>Tidak Tersedia</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-sm btn-primary-griya flex-grow-1">Filter</button>
            <a href="{{ route('pemilik.kamar.index') }}" class="btn btn-sm btn-light border"><i class="bi bi-arrow-counterclockwise"></i></a>
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
                            @elseif($kamar->status === 'Terisi')
                                <span class="badge bg-danger">Terisi</span>
                            @else
                                <span class="badge bg-secondary">Tidak Tersedia</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('pemilik.kamar.show', $kamar->id) }}" class="btn btn-sm btn-outline-griya py-0 px-2" title="Detail">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('pemilik.kamar.edit', $kamar->id) }}" class="btn btn-sm btn-light border py-0 px-2" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <form action="{{ route('pemilik.kamar.destroy', $kamar->id) }}" method="POST" onsubmit="return confirm('Hapus data kamar {{ $kamar->nomor_kamar }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="Hapus">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
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
