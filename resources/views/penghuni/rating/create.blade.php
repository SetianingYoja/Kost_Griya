@extends('layouts.dashboard')

@section('title', 'Beri Penilaian Layanan')

@section('content')
<div class="mb-4">
    <a href="{{ route('penghuni.rating.index') }}" class="text-decoration-none small text-muted">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Penilaian
    </a>
    <h3 class="fw-bold text-secondary mt-1 mb-0">Formulir Penilaian & Ulasan Kepuasan</h3>
    <p class="text-muted small mb-0">Masukan Anda sangat berharga untuk peningkatan mutu layanan Kost Putri Griya Ayu</p>
</div>

<div class="row g-4 justify-content-center">
    <div class="col-lg-7">
        <div class="card-griya p-4 p-md-5">
            <form action="{{ route('penghuni.rating.store') }}" method="POST">
                @csrf

                @if($keluhan)
                    <input type="hidden" name="keluhan_id" value="{{ $keluhan->id }}">
                    <input type="hidden" name="jenis_rating" value="Penanganan Keluhan">
                    <div class="p-3 bg-light rounded-3 border mb-4">
                        <strong class="d-block text-secondary small mb-1">Penilaian untuk Penanganan Keluhan:</strong>
                        <h5 class="fw-bold text-primary mb-1">{{ $keluhan->judul }}</h5>
                        <p class="text-muted small mb-0">Diselesaikan pada: {{ $keluhan->selesai_pada ? $keluhan->selesai_pada->format('d M Y') : '' }}</p>
                    </div>
                @else
                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-secondary">Pilih Jenis Penilaian</label>
                        <select name="jenis_rating" class="form-select">
                            <option value="Kost">Kepuasan Fasilitas & Lingkungan Kost</option>
                        </select>
                    </div>
                @endif

                <div class="mb-4 text-center">
                    <label class="form-label small fw-semibold text-secondary d-block mb-3">Pilih Skor Bintang (1 - 5)</label>
                    <div class="d-flex justify-content-center gap-3">
                        @for($i = 1; $i <= 5; $i++)
                            <div class="form-check form-check-inline p-0 m-0 text-center">
                                <input class="btn-check" type="radio" name="skor" id="star{{ $i }}" value="{{ $i }}" {{ $i == 5 ? 'checked' : '' }} required>
                                <label class="btn btn-outline-warning rounded-circle p-3 d-flex flex-column align-items-center justify-content-center" for="star{{ $i }}" style="width: 54px; height: 54px;">
                                    <i class="bi bi-star-fill fs-5"></i>
                                    <span class="small fw-bold">{{ $i }}</span>
                                </label>
                            </div>
                        @endfor
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-semibold text-secondary">Ulasan & Komentar Anda</label>
                    <textarea name="komentar" rows="4" class="form-control" placeholder="Tuliskan pengalaman Anda mengenai kebersihan, kenyamanan, atau respon petugas..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary-griya w-100 py-3 fw-semibold">
                    <i class="bi bi-check2-circle me-2"></i> Kirim Penilaian
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
