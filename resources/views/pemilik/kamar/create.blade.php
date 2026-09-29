@extends('layouts.dashboard')

@section('title', 'Tambah Kamar Baru')

@section('content')
<div class="mb-4">
    <a href="{{ route('pemilik.kamar.index') }}" class="text-decoration-none small text-muted">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Kamar
    </a>
    <h3 class="fw-bold text-secondary mt-1 mb-0">Tambah Kamar Kost Baru</h3>
</div>

<div class="row g-4 justify-content-center">
    <div class="col-lg-8">
        <div class="card-griya p-4 p-md-5">
            <form action="{{ route('pemilik.kamar.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">Nomor / Nama Kamar</label>
                        <input type="text" name="nomor_kamar" class="form-control @error('nomor_kamar') is-invalid @enderror" value="{{ old('nomor_kamar') }}" placeholder="Contoh: Kamar 104" required>
                        @error('nomor_kamar')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">Tipe Kamar</label>
                        <select id="tipe_kamar_id" name="tipe_kamar_id" class="form-select" required>
                            <option value="">Pilih Tipe</option>
                            @foreach($tipeKamars as $tipe)
                                <option value="{{ $tipe->id }}" data-harga-bulanan="{{ $tipe->harga_bulanan }}" {{ old('tipe_kamar_id') == $tipe->id ? 'selected' : '' }}>
                                    {{ $tipe->nama_tipe }} (Dasar: Rp {{ number_format($tipe->harga_bulanan, 0, ',', '.') }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-secondary">Posisi Lantai</label>
                        <input type="number" name="lantai" class="form-control" value="{{ old('lantai', 1) }}" min="1" max="10" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-secondary">Tarif Sewa (Rp / Bulan)</label>
                        <input id="harga" type="number" name="harga" class="form-control" value="{{ old('harga', '') }}" min="0" step="10000" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-secondary">Status Awal</label>
                        <select name="status" class="form-select" required>
                            <option value="Tersedia" selected>Tersedia</option>
                            <option value="Terisi">Terisi</option>
                            <option value="Tidak tersedia">Tidak Tersedia</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Fasilitas Kamar (Pisahkan dengan koma)</label>
                    <textarea name="fasilitas" rows="2" class="form-control" placeholder="Contoh: AC 0.5 PK, Kamar Mandi Dalam, Kasur Springbed 120x200, Lemari 2 Pintu, Meja Kerja">{{ old('fasilitas') }}</textarea>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Deskripsi Kamar</label>
                    <textarea name="deskripsi" rows="3" class="form-control" placeholder="Jelaskan keunggulan pencahayaan, posisi lorong, jendela, dll...">{{ old('deskripsi') }}</textarea>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-semibold text-secondary">Foto Utama Kamar (Opsional)</label>
                    <input type="file" name="foto" class="form-control" accept="image/*">
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-semibold text-secondary">Galeri Foto Kamar (Opsional)</label>
                    <input type="file" name="fotos[]" class="form-control" accept="image/*" multiple>
                    <small class="text-muted">Anda dapat menambahkan lebih dari satu foto. Semua foto akan disimpan di storage dan ditampilkan pada galeri kamar.</small>
                </div>

                <button type="submit" class="btn btn-primary-griya w-100 py-3 fw-semibold">
                    <i class="bi bi-check-circle me-2"></i> Simpan Data Kamar
                </button>
            </form>
        </div>
    </div>
</div>
<script>
    const tipeKamarSelect = document.getElementById('tipe_kamar_id');
    const hargaInput = document.getElementById('harga');

    const updateHarga = () => {
        const selectedOption = tipeKamarSelect.options[tipeKamarSelect.selectedIndex];
        if (selectedOption.dataset.hargaBulanan) {
            hargaInput.value = selectedOption.dataset.hargaBulanan;
        }
    };

    tipeKamarSelect.addEventListener('change', updateHarga);

    if (!hargaInput.value && tipeKamarSelect.value) {
        updateHarga();
    }
</script>
@endsection
