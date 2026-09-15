@extends('layouts.dashboard')

@section('title', 'Edit Data Kamar')

@section('content')
<div class="mb-4">
    <a href="{{ route('pemilik.kamar.index') }}" class="text-decoration-none small text-muted">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Kamar
    </a>
    <h3 class="fw-bold text-secondary mt-1 mb-0">Edit Data: {{ $kamar->nomor_kamar }}</h3>
</div>

<div class="row g-4 justify-content-center">
    <div class="col-lg-8">
        <div class="card-griya p-4 p-md-5">
            <form action="{{ route('pemilik.kamar.update', $kamar->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">Nomor / Nama Kamar</label>
                        <input type="text" name="nomor_kamar" class="form-control @error('nomor_kamar') is-invalid @enderror" value="{{ old('nomor_kamar', $kamar->nomor_kamar) }}" required>
                        @error('nomor_kamar')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">Tipe Kamar</label>
                        <select name="tipe_kamar_id" class="form-select" required>
                            @foreach($tipeKamars as $tipe)
                                <option value="{{ $tipe->id }}" {{ old('tipe_kamar_id', $kamar->tipe_kamar_id) == $tipe->id ? 'selected' : '' }}>
                                    {{ $tipe->nama_tipe }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-secondary">Posisi Lantai</label>
                        <input type="number" name="lantai" class="form-control" value="{{ old('lantai', $kamar->lantai) }}" min="1" max="10" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-secondary">Tarif Sewa (Rp / Bulan)</label>
                        <input type="number" name="harga" class="form-control" value="{{ old('harga', $kamar->harga) }}" min="0" step="10000" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-secondary">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="Tersedia" {{ old('status', $kamar->status) == 'Tersedia' ? 'selected' : '' }}>Tersedia</option>
                            <option value="Terisi" {{ old('status', $kamar->status) == 'Terisi' ? 'selected' : '' }}>Terisi</option>
                            <option value="Tidak tersedia" {{ old('status', $kamar->status) == 'Tidak tersedia' ? 'selected' : '' }}>Tidak Tersedia</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Fasilitas Kamar (Pisahkan dengan koma)</label>
                    <textarea name="fasilitas" rows="2" class="form-control">{{ old('fasilitas', $kamar->fasilitas) }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Deskripsi Kamar</label>
                    <textarea name="deskripsi" rows="3" class="form-control">{{ old('deskripsi', $kamar->deskripsi) }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-semibold text-secondary">Ganti Foto Utama Kamar (Opsional)</label>
                    <input type="file" name="foto" class="form-control" accept="image/*">
                    @if($kamar->foto)
                        <div class="mt-2">
                            <small class="text-muted d-block mb-1">Foto utama saat ini:</small>
                            <img src="{{ $kamar->foto_url }}" alt="Preview" class="rounded" style="width: 100px; height: 70px; object-fit: cover;">
                        </div>
                    @endif
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-semibold text-secondary">Tambah Foto Galeri Kamar (Opsional)</label>
                    <input type="file" name="fotos[]" class="form-control" accept="image/*" multiple>
                    <small class="text-muted">Anda dapat menambahkan satu atau beberapa foto baru ke galeri kamar tanpa membatasi jumlah.</small>
                </div>

                <button type="submit" class="btn btn-primary-griya w-100 py-3 fw-semibold">
                    <i class="bi bi-check-circle me-2"></i> Perbarui Data Kamar
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
