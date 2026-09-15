@extends('layouts.dashboard')

@section('title', 'Verifikasi Pembayaran ' . $pembayaran->kode_pembayaran)

@section('content')
<div class="mb-4">
    <a href="{{ route('pemilik.pembayaran.index') }}" class="text-decoration-none small text-muted">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Pembayaran
    </a>
    <h3 class="fw-bold text-secondary mt-1 mb-0">Verifikasi Bukti Transfer: {{ $pembayaran->kode_pembayaran }}</h3>
</div>

<div class="row g-4">
    <div class="col-lg-7">
        <div class="card-griya p-4 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                <span class="badge bg-light text-primary border px-3 py-2 fw-semibold fs-6">{{ $pembayaran->jenis_pembayaran }}</span>
                @if($pembayaran->status === 'Lunas')
                    <span class="badge bg-success px-3 py-2 fs-6">Lunas</span>
                @elseif($pembayaran->status === 'Ditolak')
                    <span class="badge bg-danger px-3 py-2 fs-6">Ditolak</span>
                @else
                    <span class="badge bg-warning text-dark px-3 py-2 fs-6">Menunggu Validasi</span>
                @endif
            </div>

            <div class="row g-3 small mb-4">
                <div class="col-sm-6">
                    <span class="text-muted d-block">Nama Pengirim:</span>
                    <strong>{{ $pembayaran->nama_pengirim }}</strong>
                </div>
                <div class="col-sm-6">
                    <span class="text-muted d-block">Bank Pengirim:</span>
                    <strong>{{ $pembayaran->bank_pengirim }}</strong>
                </div>
                <div class="col-sm-6">
                    <span class="text-muted d-block">Tanggal Transfer:</span>
                    <strong>{{ $pembayaran->tanggal_bayar ? $pembayaran->tanggal_bayar->format('d F Y') : '-' }}</strong>
                </div>
                <div class="col-sm-6">
                    <span class="text-muted d-block">Nominal Tertera:</span>
                    <strong class="fs-4 text-primary">Rp {{ number_format($pembayaran->nominal, 0, ',', '.') }}</strong>
                </div>
            </div>

            @if($pembayaran->booking)
                <div class="p-3 bg-light rounded-3 mb-3 small">
                    <strong class="text-secondary d-block mb-1">Terkait Booking:</strong>
                    <span>{{ $pembayaran->booking->kode_booking }} — Kamar: <strong>{{ $pembayaran->booking->kamar->nomor_kamar ?? '-' }}</strong> ({{ $pembayaran->booking->durasi_bulan }} Bulan)</span>
                </div>
            @endif

            @if($pembayaran->tagihan)
                <div class="p-3 bg-light rounded-3 mb-3 small">
                    <strong class="text-secondary d-block mb-1">Terkait Tagihan:</strong>
                    <span>{{ $pembayaran->tagihan->nomor_tagihan }} — Periode: <strong>{{ $pembayaran->tagihan->periode }}</strong></span>
                </div>
            @endif

            @if($pembayaran->perpanjangan)
                <div class="p-3 bg-light rounded-3 mb-3 small">
                    <strong class="text-secondary d-block mb-1">Terkait DP Perpanjangan (Model B):</strong>
                    <span>Durasi: {{ $pembayaran->perpanjangan->durasi_bulan }} Bulan (Kewajiban DP: Rp {{ number_format($pembayaran->perpanjangan->nominal_dp, 0, ',', '.') }})</span>
                </div>
            @endif

            @if($pembayaran->catatan_penghuni)
                <div class="p-3 bg-light rounded-3 mb-3 small">
                    <strong class="text-secondary d-block mb-1">Catatan Penghuni:</strong>
                    <span>{{ $pembayaran->catatan_penghuni }}</span>
                </div>
            @endif

            @if($pembayaran->catatan_pemilik)
                <div class="alert alert-secondary small mb-3">
                    <strong class="d-block mb-1">Catatan Pemilik:</strong>
                    <span>{{ $pembayaran->catatan_pemilik }}</span>
                </div>
            @endif

            <!-- Validasi Actions -->
            @if($pembayaran->status !== 'Lunas')
                <div class="p-4 bg-light rounded-3 border mt-4">
                    <h5 class="fw-bold text-secondary mb-3">Tindakan Validasi Transfer:</h5>
                    <div class="d-flex flex-wrap gap-2">
                        <form action="{{ route('pemilik.pembayaran.approve', $pembayaran->id) }}" method="POST" onsubmit="return confirm('Validasi pembayaran ini sebagai Lunas? Sistem akan otomatis mengaktifkan status sewa dan kamar.')">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="btn btn-success px-4">
                                <i class="bi bi-check2-circle me-1"></i> Validasi Sebagai LUNAS
                            </button>
                        </form>

                        <button type="button" class="btn btn-outline-danger px-4" data-bs-toggle="collapse" data-bs-target="#rejectForm">
                            <i class="bi bi-x-circle me-1"></i> Tolak Pembayaran
                        </button>
                    </div>

                    <div class="collapse mt-3" id="rejectForm">
                        <form action="{{ route('pemilik.pembayaran.reject', $pembayaran->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <label class="form-label small fw-semibold text-secondary">Alasan Penolakan (Misal: mutasi tidak ditemukan / nominal kurang):</label>
                            <textarea name="catatan_pemilik" rows="3" class="form-control mb-2" placeholder="Jelaskan alasan penolakan..." required></textarea>
                            <button type="submit" class="btn btn-danger btn-sm">Kirim Penolakan</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <!-- Lampiran Bukti Transfer -->
    <div class="col-lg-5">
        <div class="card-griya p-4">
            <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-image me-2 text-primary"></i>Foto / Bukti Transfer</h5>
            @if($pembayaran->bukti_pembayaran)
                <div class="border rounded-3 p-2 bg-light text-center mb-3">
                    <img src="{{ $pembayaran->bukti_url }}" alt="Bukti Transfer" class="img-fluid rounded" style="max-height: 450px;">
                </div>
                <div class="text-center">
                    <a href="{{ $pembayaran->bukti_url }}" target="_blank" class="btn btn-sm btn-outline-griya">
                        <i class="bi bi-arrows-fullscreen me-1"></i> Lihat Ukuran Penuh
                    </a>
                </div>
            @else
                <p class="text-muted small">Tidak ada file bukti pembayaran terlampir.</p>
            @endif
        </div>
    </div>
</div>
@endsection
