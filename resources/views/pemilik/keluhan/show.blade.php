@extends('layouts.dashboard')

@section('title', 'Tanggapi Keluhan ' . $keluhan->kode_keluhan)

@section('content')
<div class="mb-4">
    <a href="{{ route('pemilik.keluhan.index') }}" class="text-decoration-none small text-muted">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Keluhan
    </a>
    <h3 class="fw-bold text-secondary mt-1 mb-0">Tanggapan Keluhan: {{ $keluhan->kode_keluhan }}</h3>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card-griya p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                <div>
                    <h4 class="fw-bold text-secondary mb-1">{{ $keluhan->judul }}</h4>
                    <span class="text-muted small">Pelapor: <strong>{{ $keluhan->user->name }}</strong> (Kamar {{ $keluhan->kamar->nomor_kamar ?? 'Area Umum' }})</span>
                </div>
                <div>
                    @if($keluhan->status === 'Selesai')
                        <span class="badge bg-success fs-6 px-3 py-2">Selesai</span>
                    @elseif($keluhan->status === 'Diproses')
                        <span class="badge bg-info text-dark fs-6 px-3 py-2">Diproses</span>
                    @elseif($keluhan->status === 'Ditolak')
                        <span class="badge bg-danger fs-6 px-3 py-2">Ditolak</span>
                    @else
                        <span class="badge bg-warning text-dark fs-6 px-3 py-2">Menunggu</span>
                    @endif
                </div>
            </div>

            <div class="mb-4">
                <h6 class="fw-bold text-secondary mb-2">Uraian Masalah dari Penghuni:</h6>
                <div class="p-3 bg-light rounded-3 text-secondary leading-relaxed">
                    {{ $keluhan->isi }}
                </div>
            </div>

            @if($keluhan->foto)
                <div class="mb-4">
                    <h6 class="fw-bold text-secondary mb-2">Foto Lampiran Penghuni:</h6>
                    <img src="{{ $keluhan->foto_url }}" alt="Foto Keluhan" class="img-fluid rounded border" style="max-height: 320px;">
                </div>
            @endif

            <!-- Form Penanganan & Tanggapan Pemilik -->
            <div class="p-4 bg-light rounded-3 border">
                <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-tools text-primary me-2"></i>Form Penanganan & Status</h5>
                @if(Auth::user()->isSuperAdmin())
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Pembaruan Status Penanganan</label>
                        <div class="form-control bg-white">{{ $keluhan->status }}</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Tanggapan Resmi untuk Penghuni</label>
                        <div class="form-control bg-white" style="min-height: 100px;">{{ $keluhan->tanggapan ?: 'Belum ada tanggapan.' }}</div>
                    </div>
                    
                    <div class="alert alert-info small mb-0"><i class="bi bi-info-circle me-1"></i> Mode View Only. Anda masuk sebagai Super Admin. Penanganan keluhan hanya dapat dilakukan oleh Pemilik Kost.</div>
                @else
                    <form action="{{ route('pemilik.keluhan.update', $keluhan->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">Pembaruan Status Penanganan</label>
                            <select name="status" class="form-select" required>
                                <option value="Menunggu" {{ $keluhan->status === 'Menunggu' ? 'selected' : '' }}>Menunggu Tanggapan</option>
                                <option value="Diproses" {{ $keluhan->status === 'Diproses' ? 'selected' : '' }}>Sedang Ditangani / Teknisi Meluncur</option>
                                <option value="Selesai" {{ $keluhan->status === 'Selesai' ? 'selected' : '' }}>Selesai Diperbaiki</option>
                                <option value="Ditolak" {{ $keluhan->status === 'Ditolak' ? 'selected' : '' }}>Ditolak (Di luar kewajiban / keliru)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">Tulis Tanggapan Resmi untuk Penghuni</label>
                            <textarea name="tanggapan" rows="4" class="form-control" placeholder="Contoh: Teknisi telah memeriksa unit AC dan mengganti freon..." required>{{ old('tanggapan', $keluhan->tanggapan) }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary-griya px-4">
                            <i class="bi bi-save me-1"></i> Simpan Status & Kirim Tanggapan
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Rating dari Penghuni (jika sudah dinilai) -->
    <div class="col-lg-5">
        <div class="card-griya p-4">
            <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-star text-warning me-2"></i>Penilaian Penghuni</h5>
            @if($keluhan->rating)
                <div class="p-3 bg-light rounded-3 border">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <div class="text-warning fs-5">
                            @for($i=1;$i<=5;$i++)
                                <i class="bi bi-star{{ $i <= $keluhan->rating->skor ? '-fill' : '' }}"></i>
                            @endfor
                        </div>
                        <strong class="text-secondary">({{ $keluhan->rating->skor }} / 5 Bintang)</strong>
                    </div>
                    <p class="text-muted small mb-0">"{{ $keluhan->rating->komentar ?: 'Tidak ada ulasan tertulis.' }}"</p>
                </div>
            @else
                <p class="text-muted small mb-0">Penghuni belum memberikan penilaian untuk penanganan keluhan ini.</p>
            @endif
        </div>
    </div>
</div>
@endsection
