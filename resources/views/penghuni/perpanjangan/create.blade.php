@extends('layouts.dashboard')

@section('title', 'Ajukan Perpanjangan Masa Sewa')

@section('content')
<div class="mb-4">
    <a href="{{ route('penghuni.perpanjangan.index') }}" class="text-decoration-none small text-muted">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Riwayat Perpanjangan
    </a>
    <h3 class="fw-bold text-secondary mt-1 mb-0">Formulir Pengajuan Perpanjangan Sewa</h3>
    <p class="text-muted small mb-0">Skema Model B: Bayar DP terlebih dahulu, sisa sewa masuk ke tagihan bulanan</p>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card-griya p-4 p-md-5">
            <h5 class="fw-bold text-secondary mb-3 pb-2 border-bottom">Data Pengajuan</h5>

            <form action="{{ route('penghuni.perpanjangan.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Kamar yang Sedang Disewa</label>
                    <input type="text" class="form-control bg-light" value="{{ $activeSewa->kamar->nomor_kamar }} ({{ $activeSewa->kamar->tipeKamar->nama_tipe ?? '' }}) - Rp {{ number_format($activeSewa->harga_per_bulan, 0, ',', '.') }}/bulan" disabled>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">Tanggal Berakhir Kontrak Saat Ini</label>
                        <input type="text" class="form-control bg-light" value="{{ $activeSewa->tanggal_selesai->format('d F Y') }}" disabled>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">Pilih Durasi Perpanjangan</label>
                        <select name="durasi_bulan" id="durasi_bulan" class="form-select" required>
                            <option value="1">1 Bulan</option>
                            <option value="3">3 Bulan</option>
                            <option value="6">6 Bulan</option>
                            <option value="12">12 Bulan (1 Tahun)</option>
                        </select>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-semibold text-secondary">Catatan untuk Pengelola (Opsional)</label>
                    <textarea name="catatan" rows="3" class="form-control" placeholder="Tuliskan jika ada permintaan khusus saat perpanjangan..."></textarea>
                </div>

                <div class="alert alert-light border small text-muted mb-4">
                    <i class="bi bi-info-circle text-primary me-1"></i>
                    <strong>Ketentuan Model B:</strong> Setelah permohonan disetujui, Anda cukup membayar <strong>DP (Uang Muka)</strong> terlebih dahulu untuk mengunci perpanjangan kamar. DP tersebut akan langsung memotong kewajiban sewa sehingga <strong>tidak terjadi double charge</strong>.
                </div>

                <button type="submit" class="btn btn-primary-griya w-100 py-3 fw-semibold">
                    <i class="bi bi-send me-2"></i> Kirim Pengajuan Perpanjangan
                </button>
            </form>
        </div>
    </div>

    <div class="col-lg-5">
        <div class="card-griya p-4 border-primary">
            <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-shield-check text-primary me-2"></i>Keuntungan Model B</h5>
            <div class="d-flex flex-column gap-3 small text-muted">
                <div class="d-flex align-items-start gap-2">
                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                    <span><strong>Kamar Tetap Aman:</strong> Kamar Anda otomatis terkunci dan tidak bisa dibooking oleh calon penghuni lain.</span>
                </div>
                <div class="d-flex align-items-start gap-2">
                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                    <span><strong>Fleksibel Finansial:</strong> Tidak perlu langsung melunasi penuh di awal, cukup bayar DP terlebih dahulu.</span>
                </div>
                <div class="d-flex align-items-start gap-2">
                    <i class="bi bi-check-circle-fill text-success fs-5"></i>
                    <span><strong>Otomatis & Transparan:</strong> Sisa tagihan bulanan langsung dipotong DP secara otomatis pada invoice sistem.</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
