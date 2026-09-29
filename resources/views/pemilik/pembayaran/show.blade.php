@extends('layouts.dashboard')

@php
    $isQris = $pembayaran->metode_pembayaran === 'QRIS';
    $booking = $pembayaran->booking;
@endphp

@section('title', 'Verifikasi Pembayaran ' . $pembayaran->kode_pembayaran)

@section('content')
<div class="mb-4">
    <a href="{{ route('pemilik.pembayaran.index') }}" class="text-decoration-none small text-muted">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Pembayaran
    </a>
    <h3 class="fw-bold text-secondary mt-1 mb-0">
        @if($isQris)
            <i class="bi bi-qr-code-scan me-2 text-primary"></i>Verifikasi Pembayaran QRIS: {{ $pembayaran->kode_pembayaran }}
        @else
            Verifikasi Bukti Transfer: {{ $pembayaran->kode_pembayaran }}
        @endif
    </h3>
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

            @if($isQris)
                {{-- ======================================================== --}}
                {{-- SECTION: Detail Pembayaran QRIS Midtrans                 --}}
                {{-- ======================================================== --}}
                <div class="row g-3 small mb-4">
                    <div class="col-sm-6">
                        <span class="text-muted d-block">Metode Pembayaran:</span>
                        <strong class="text-primary"><i class="bi bi-qr-code me-1"></i>QRIS (Midtrans)</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block">Nominal Pembayaran:</span>
                        <strong class="fs-4 text-primary">Rp {{ number_format($pembayaran->nominal, 0, ',', '.') }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block">Midtrans Order ID:</span>
                        @php
                            preg_match('/Order ID: ([^\)]+)/', $pembayaran->catatan_penghuni ?? '', $orderMatches);
                            $extractedOrderId = $orderMatches[1] ?? ($booking?->midtrans_order_id ?? '-');
                        @endphp
                        <strong class="font-monospace small">{{ $extractedOrderId }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block">Midtrans Transaction ID:</span>
                        <strong class="font-monospace small">{{ $pembayaran->midtrans_transaction_id ?? '-' }}</strong>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block">Status Midtrans:</span>
                        @php
                            $midStatus = $booking?->midtrans_status ?? ($pembayaran->midtrans_transaction_id ? 'settlement' : '-');
                        @endphp
                        <span class="badge
                            @if($midStatus === 'settlement') bg-success
                            @elseif($midStatus === 'pending') bg-warning text-dark
                            @elseif(in_array($midStatus, ['cancel','deny','failure','expire'])) bg-danger
                            @else bg-secondary
                            @endif px-2 py-1">
                            {{ strtoupper($midStatus) }}
                        </span>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted d-block">Waktu Pembayaran:</span>
                        <strong>
                            @if($booking?->midtrans_paid_at)
                                {{ \Carbon\Carbon::parse($booking->midtrans_paid_at)->format('d F Y, H:i') }} WIB
                            @elseif($pembayaran->tanggal_bayar)
                                {{ $pembayaran->tanggal_bayar->format('d F Y') }}
                            @else
                                -
                            @endif
                        </strong>
                    </div>
                </div>

                {{-- Info verifikasi QRIS --}}
                <div class="alert alert-info small d-flex align-items-start gap-2 mb-0">
                    <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
                    <span>Pembayaran ini dilakukan melalui <strong>QRIS Midtrans</strong>. Status <strong>SETTLEMENT</strong> dari Midtrans menandakan dana sudah diterima. Tidak diperlukan bukti transfer manual.</span>
                </div>

            @else
                {{-- ======================================================== --}}
                {{-- SECTION: Detail Pembayaran Transfer Manual                --}}
                {{-- ======================================================== --}}
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
            @endif

            @if($booking)
                <div class="p-3 bg-light rounded-3 mb-3 small">
                    <strong class="text-secondary d-block mb-1">Terkait Booking:</strong>
                    <span>{{ $booking->kode_booking }} — Kamar: <strong>{{ $booking->kamar->nomor_kamar ?? '-' }}</strong> ({{ $booking->durasi_bulan }} Bulan)</span>
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
                    <strong class="text-secondary d-block mb-1">Terkait Perpanjangan Sewa ({{ ($pembayaran->perpanjangan->tipe_pembayaran ?? 'DP') === 'Lunas' ? 'LUNAS' : 'DP' }}):</strong>
                    <span>
                        Durasi: {{ $pembayaran->perpanjangan->durasi_bulan }} Bulan 
                        @if(($pembayaran->perpanjangan->tipe_pembayaran ?? 'DP') === 'Lunas')
                            (Nominal Lunas: Rp {{ number_format($pembayaran->perpanjangan->nominal_total, 0, ',', '.') }})
                        @else
                            (Kewajiban DP: Rp {{ number_format($pembayaran->perpanjangan->nominal_dp, 0, ',', '.') }})
                        @endif
                    </span>
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

            {{-- Validasi Actions --}}
            @if($pembayaran->status !== 'Lunas')
                <div class="p-4 bg-light rounded-3 border mt-4">
                    <h5 class="fw-bold text-secondary mb-3">
                        @if($isQris)
                            <i class="bi bi-check2-all me-1"></i>Tindakan Validasi QRIS:
                        @else
                            Tindakan Validasi Transfer:
                        @endif
                    </h5>
                    <div class="d-flex flex-wrap gap-2">
                        <form action="{{ route('pemilik.pembayaran.approve', $pembayaran->id) }}" method="POST"
                              onsubmit="return confirm('Validasi pembayaran ini sebagai Lunas? Sistem akan otomatis mengaktifkan status sewa dan kamar.')">
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
                            <label class="form-label small fw-semibold text-secondary">
                                @if($isQris)
                                    Alasan Penolakan (Misal: transaksi tidak valid / status Midtrans tidak sesuai):
                                @else
                                    Alasan Penolakan (Misal: mutasi tidak ditemukan / nominal kurang):
                                @endif
                            </label>
                            <textarea name="catatan_pemilik" rows="3" class="form-control mb-2"
                                      placeholder="Jelaskan alasan penolakan..." required></textarea>
                            <button type="submit" class="btn btn-danger btn-sm">Kirim Penolakan</button>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Kolom Kanan: Bukti Transfer (hanya untuk pembayaran manual) --}}
    @if(!$isQris)
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
    @endif
</div>
@endsection
