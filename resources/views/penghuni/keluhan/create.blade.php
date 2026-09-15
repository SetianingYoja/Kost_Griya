@extends('layouts.dashboard')

@section('title', 'Buat Keluhan Baru')

@section('content')
<div class="mb-4">
    <a href="{{ route('penghuni.keluhan.index') }}" class="text-decoration-none small text-muted">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Keluhan
    </a>
    <h3 class="fw-bold text-secondary mt-1 mb-0">Laporkan Kendala atau Kerusakan Fasilitas</h3>
    <p class="text-muted small mb-0">Tim pengelola Kost Griya Ayu siap membantu penanganan secepat mungkin</p>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-griya p-4 p-md-5">
            <form action="{{ route('penghuni.keluhan.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Judul Keluhan / Kendala</label>
                    <input type="text" name="judul" class="form-control @error('judul') is-invalid @enderror" value="{{ old('judul') }}" placeholder="Contoh: AC tidak dingin / Kran air wastafel bocor" required>
                    @error('judul')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Kamar Terkait</label>
                    <input type="text" class="form-control bg-light" value="{{ $activeSewa ? $activeSewa->kamar->nomor_kamar : 'Area Kost Umum' }}" disabled>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Uraian Detail Kendala</label>
                    <textarea name="isi" rows="4" class="form-control @error('isi') is-invalid @enderror" placeholder="Jelaskan secara detail kendala yang dialami agar staf dapat mempersiapkan peralatan yang tepat..." required>{{ old('isi') }}</textarea>
                    @error('isi')
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-semibold text-secondary">Foto Bukti Kendala (Opsional)</label>
                    <input type="file" name="foto" class="form-control" accept="image/*">
                    <div class="form-text small text-muted">Lampirkan foto kerusakan untuk mempercepat diagnosa teknisi. Maks: 3 MB.</div>
                </div>

                <button type="submit" class="btn btn-primary-griya w-100 py-3 fw-semibold">
                    <i class="bi bi-send me-2"></i> Kirim Laporan Keluhan
                </button>
            </form>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-griya p-4">
            <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-clock text-primary me-2"></i>Waktu Penanganan</h5>
            <p class="small text-muted mb-3">
                Laporan keluhan yang masuk pada jam kerja (08.00 - 17.00 WIB) akan langsung ditindaklanjuti oleh staf pengelola atau teknisi di hari yang sama.
            </p>
            <div class="alert alert-light border small text-secondary mb-0">
                <i class="bi bi-telephone-fill text-success me-1"></i>
                Untuk kondisi darurat (contoh: pipa air jebol atau korsleting listrik), silakan langsung hubungi resepsionis via WhatsApp: <strong>+62 822-4264-5466</strong>.
            </div>
        </div>
    </div>
</div>
@endsection
