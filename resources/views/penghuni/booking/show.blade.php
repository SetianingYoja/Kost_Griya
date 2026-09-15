@extends('layouts.dashboard')

@section('title', 'Detail Booking ' . $booking->kode_booking)

@section('content')
<div class="mb-4">
    <a href="{{ route('penghuni.booking.index') }}" class="text-decoration-none small text-muted">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Booking
    </a>
    <h3 class="fw-bold text-secondary mt-1 mb-0">Detail Pemesanan: {{ $booking->kode_booking }}</h3>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-griya p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                <span class="badge bg-light text-primary border px-3 py-2 fw-semibold">
                    {{ $booking->kamar->nomor_kamar }} ({{ $booking->kamar->tipeKamar->nama_tipe ?? '' }})
                </span>
                <span class="badge bg-primary px-3 py-2">{{ $booking->status }}</span>
            </div>

            <div class="row g-3 small mb-4">
                <div class="col-sm-6">
                    <span class="text-muted d-block">Tanggal Mulai Sewa:</span>
                    <strong>{{ $booking->tanggal_mulai->format('d F Y') }}</strong>
                </div>
                <div class="col-sm-6">
                    <span class="text-muted d-block">Durasi Sewa:</span>
                    <strong>{{ $booking->durasi_bulan }} Bulan</strong>
                </div>
                <div class="col-sm-6">
                    <span class="text-muted d-block">Tarif per Bulan:</span>
                    <strong>Rp {{ number_format($booking->kamar->harga, 0, ',', '.') }}</strong>
                </div>
                <div class="col-sm-6">
                    <span class="text-muted d-block">Total Kewajiban Pembayaran:</span>
                    <strong class="text-primary fs-5">Rp {{ number_format($booking->total_harga, 0, ',', '.') }}</strong>
                </div>
            </div>

            @if($booking->catatan)
                <div class="p-3 bg-light rounded-3 mb-4 small">
                    <strong class="text-secondary d-block mb-1">Catatan Anda:</strong>
                    <span class="text-muted">{{ $booking->catatan }}</span>
                </div>
            @endif

            @if($booking->alasan_penolakan)
                <div class="alert alert-danger mb-4 small">
                    <strong class="d-block mb-1"><i class="bi bi-x-circle me-1"></i> Alasan Penolakan dari Pemilik:</strong>
                    {{ $booking->alasan_penolakan }}
                </div>
            @endif

            @if(in_array($booking->status, ['Menunggu Validasi', 'Disetujui', 'Menunggu Pembayaran']))
                <form action="{{ route('penghuni.booking.cancel', $booking->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan booking ini?')">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-x-lg me-1"></i> Batalkan Pemesanan Ini
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-griya p-4">
            <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-wallet2 text-primary me-2"></i>Status Pembayaran</h5>
            @if(in_array($booking->status, ['Menunggu Pembayaran', 'Disetujui']))
                <p class="small text-muted mb-3">Booking Anda telah disetujui. Silakan unggah bukti transfer sebelum batas waktu habis.</p>
                <a href="{{ route('penghuni.pembayaran.create', ['booking_id' => $booking->id]) }}" class="btn btn-primary-griya w-100 mb-2">
                    <i class="bi bi-upload me-1"></i> Bayar Sekarang
                </a>
            @elseif($booking->status === 'Menunggu Validasi')
                <p class="small text-muted mb-0">Permohonan Anda sedang dalam antrean pemeriksaan pemilik kost.</p>
            @elseif($booking->status === 'Selesai')
                <div class="alert alert-success small mb-0">
                    <i class="bi bi-check-circle-fill me-1"></i> Pembayaran telah diverifikasi LUNAS dan sewa kamar aktif.
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
