@extends('layouts.dashboard')

@section('title', 'Periksa Booking ' . $booking->kode_booking)

@section('content')
<div class="mb-4">
    <a href="{{ route('pemilik.booking.index') }}" class="text-decoration-none small text-muted">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Booking
    </a>
    <h3 class="fw-bold text-secondary mt-1 mb-0">Verifikasi Pemesanan: {{ $booking->kode_booking }}</h3>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-griya p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                <span class="badge bg-light text-primary border px-3 py-2 fw-semibold">
                    {{ $booking->kamar->nomor_kamar }} ({{ $booking->kamar->tipeKamar->nama_tipe ?? '' }})
                </span>
                <span class="badge bg-primary px-3 py-2 fs-6">{{ $booking->status }}</span>
            </div>

            <div class="row g-3 small mb-4">
                <div class="col-sm-6">
                    <span class="text-muted d-block">Nama Calon Penghuni:</span>
                    <strong>{{ $booking->user->name }}</strong>
                </div>
                <div class="col-sm-6">
                    <span class="text-muted d-block">Kontak WhatsApp:</span>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $booking->user->phone) }}" target="_blank" class="text-success fw-bold text-decoration-none">
                        <i class="bi bi-whatsapp me-1"></i> {{ $booking->user->phone }}
                    </a>
                </div>
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
                    <span class="text-muted d-block">Total Nominal Kewajiban:</span>
                    <strong class="fs-4 text-primary">Rp {{ number_format($booking->total_harga, 0, ',', '.') }}</strong>
                </div>
            </div>

            @if($booking->catatan)
                <div class="p-3 bg-light rounded-3 mb-4 small">
                    <strong class="text-secondary d-block mb-1">Catatan Pemohon:</strong>
                    <span class="text-muted">{{ $booking->catatan }}</span>
                </div>
            @endif

            @if($booking->alasan_penolakan)
                <div class="alert alert-danger small mb-4">
                    <strong class="d-block mb-1"><i class="bi bi-x-circle me-1"></i> Alasan Penolakan:</strong>
                    {{ $booking->alasan_penolakan }}
                </div>
            @endif

            <!-- Form Approval / Rejection jika Menunggu Validasi -->
            @if($booking->status === 'Menunggu Validasi')
                <div class="p-4 bg-light rounded-3 border mt-4">
                    <h5 class="fw-bold text-secondary mb-3">Tindakan Validasi Pemilik Kost:</h5>
                    <div class="d-flex flex-wrap gap-2">
                        <form action="{{ route('pemilik.booking.approve', $booking->id) }}" method="POST" onsubmit="return confirm('Setujui booking ini? Status akan beralih ke Menunggu Pembayaran.')">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success px-4">
                                <i class="bi bi-check-circle me-1"></i> Setujui Booking
                            </button>
                        </form>

                        <button type="button" class="btn btn-outline-danger px-4" data-bs-toggle="collapse" data-bs-target="#rejectForm">
                            <i class="bi bi-x-circle me-1"></i> Tolak Booking
                        </button>
                    </div>

                    <div class="collapse mt-3" id="rejectForm">
                        <form action="{{ route('pemilik.booking.reject', $booking->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <label class="form-label small fw-semibold text-secondary">Alasan Penolakan (Akan dikirimkan ke calon penghuni):</label>
                            <textarea name="alasan_penolakan" rows="3" class="form-control mb-2" placeholder="Contoh: Kamar sedang perbaikan teknis / jadwal mulai sewa tidak sesuai..." required></textarea>
                            <button type="submit" class="btn btn-danger btn-sm">Konfirmasi Tolak Booking</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-griya p-4">
            <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-info-circle text-primary me-2"></i>Ketentuan Alur</h5>
            <div class="small text-muted leading-relaxed">
                <p class="mb-2">1. Menyetujui booking akan mengubah status menjadi <strong>Menunggu Pembayaran</strong> dan memberikan batas transfer 24 jam.</p>
                <p class="mb-2">2. Status kamar <strong>belum</strong> menjadi Terisi sampai bukti pembayaran transfer divalidasi Lunas.</p>
            </div>
        </div>
    </div>
</div>
@endsection
