@extends('layouts.dashboard')

@section('title', 'Detail Keluhan ' . $keluhan->kode_keluhan)

@section('content')
<div class="mb-4">
    <a href="{{ route('penghuni.keluhan.index') }}" class="text-decoration-none small text-muted">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Keluhan
    </a>
    <h3 class="fw-bold text-secondary mt-1 mb-0">Rincian Keluhan: {{ $keluhan->kode_keluhan }}</h3>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-griya p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                <div>
                    <h4 class="fw-bold text-secondary mb-1">{{ $keluhan->judul }}</h4>
                    <span class="text-muted small">Dilaporkan pada: {{ $keluhan->created_at->format('d F Y, H:i') }} WIB</span>
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
                <h6 class="fw-bold text-secondary mb-2">Uraian Masalah:</h6>
                <div class="p-3 bg-light rounded-3 text-secondary leading-relaxed">
                    {{ $keluhan->isi }}
                </div>
            </div>

            @if($keluhan->foto)
                <div class="mb-4">
                    <h6 class="fw-bold text-secondary mb-2">Foto Lampiran:</h6>
                    <img src="{{ $keluhan->foto_url }}" alt="Foto Keluhan" class="img-fluid rounded border" style="max-height: 320px;">
                </div>
            @endif

            <!-- Tanggapan Pemilik -->
            <div class="p-4 rounded-3 border {{ $keluhan->tanggapan ? 'bg-light' : 'bg-light opacity-75' }}">
                <h5 class="fw-bold text-secondary mb-2"><i class="bi bi-chat-left-dots text-primary me-2"></i>Tanggapan / Penanganan Pemilik Kost:</h5>
                @if($keluhan->tanggapan)
                    <p class="text-secondary mb-1 leading-relaxed">{{ $keluhan->tanggapan }}</p>
                    @if($keluhan->selesai_pada)
                        <small class="text-success"><i class="bi bi-check2 me-1"></i> Diselesaikan pada: {{ $keluhan->selesai_pada->format('d M Y, H:i') }} WIB</small>
                    @endif
                @else
                    <p class="text-muted small mb-0">Belum ada respon resmi dari pengelola kost. Mohon bersabar.</p>
                @endif
            </div>

            <!-- Action Rating jika Selesai -->
            @if($keluhan->status === 'Selesai')
                <div class="mt-4 pt-3 border-top">
                    @if($keluhan->rating)
                        <div class="alert alert-success small mb-0">
                            <i class="bi bi-star-fill text-warning me-1"></i> Anda telah memberikan rating:
                            <strong>{{ $keluhan->rating->skor }} Bintang</strong> — "{{ $keluhan->rating->komentar }}"
                        </div>
                    @else
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small text-muted">Bantu kami meningkatkan kualitas penanganan dengan memberikan rating.</span>
                            <a href="{{ route('penghuni.rating.create', ['keluhan_id' => $keluhan->id]) }}" class="btn btn-primary-griya btn-sm">
                                <i class="bi bi-star me-1"></i> Beri Penilaian Penanganan
                            </a>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-griya p-4">
            <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-info-circle text-primary me-2"></i>Status Laporan</h5>
            <ul class="list-unstyled small text-muted mb-0">
                <li class="mb-2"><strong>Kode Laporan:</strong> {{ $keluhan->kode_keluhan }}</li>
                <li class="mb-2"><strong>Kamar:</strong> {{ $keluhan->kamar ? $keluhan->kamar->nomor_kamar : 'Area Kost' }}</li>
                <li class="mb-2"><strong>Pelapor:</strong> {{ $keluhan->user->name }}</li>
                <li><strong>Terakhir Diperbarui:</strong> {{ $keluhan->updated_at->diffForHumans() }}</li>
            </ul>
        </div>
    </div>
</div>
@endsection
