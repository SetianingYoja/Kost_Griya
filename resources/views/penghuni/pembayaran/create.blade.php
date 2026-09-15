@extends('layouts.dashboard')

@section('title', 'Unggah Bukti Pembayaran')

@section('content')
<div class="mb-4">
    <a href="{{ route('penghuni.dashboard') }}" class="text-decoration-none small text-muted">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
    </a>
    <h3 class="fw-bold text-secondary mt-1 mb-0">Konfirmasi & Unggah Bukti Pembayaran</h3>
    <p class="text-muted small mb-0">Pastikan transfer dilakukan ke rekening resmi Kost Putri Griya Ayu</p>
</div>

<div class="row g-4">
    <!-- Info Rekening & Rincian -->
    <div class="col-lg-5">
        <div class="card-griya p-4 mb-4">
            <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-bank me-2 text-primary"></i>Rekening Tujuan Transfer</h5>
            <div class="p-3 bg-light rounded-3 border mb-3">
                <div class="small text-muted mb-1">Nama Bank:</div>
                <h5 class="fw-bold text-secondary mb-2">{{ $kostInfo->bank_nama ?? 'Bank BCA' }}</h5>

                <div class="small text-muted mb-1">Nomor Rekening:</div>
                <div class="fs-4 fw-bold text-primary mb-2 font-monospace">{{ $kostInfo->bank_rekening ?? '8415291039' }}</div>

                <div class="small text-muted mb-1">Atas Nama:</div>
                <div class="fw-semibold text-secondary">{{ $kostInfo->bank_atas_nama ?? 'Kost Putri Griya Ayu' }}</div>
            </div>

            <div class="p-3 bg-light rounded-3 border">
                <div class="d-flex justify-content-between mb-2 small">
                    <span class="text-muted">Jenis Transaksi:</span>
                    <strong>{{ $jenis }}</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                    <span class="text-secondary fw-semibold">Nominal Wajib:</span>
                    <span class="fs-4 fw-bold text-primary">Rp {{ number_format($nominal, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <div class="card-griya p-4 border-info">
            <h6 class="fw-bold text-secondary mb-2"><i class="bi bi-info-circle text-primary me-2"></i>Panduan Pembayaran:</h6>
            <ol class="small text-muted ps-3 mb-0">
                <li>Transfer nominal sesuai rincian di atas melalui ATM / Mobile Banking / Internet Banking.</li>
                <li>Simpan struk / tangkapan layar (screenshot) bukti berhasil transfer.</li>
                <li>Isi form di samping dan lampirkan bukti transfer.</li>
                <li>Pemilik kost akan memverifikasi pembayaran Anda dalam 1x24 jam.</li>
            </ol>
        </div>
    </div>

    <!-- Form Upload Bukti -->
    <div class="col-lg-7">
        <div class="card-griya p-4 p-md-5">
            <h5 class="fw-bold text-secondary mb-4 pb-2 border-bottom">Formulir Bukti Pembayaran</h5>

            <form action="{{ route('penghuni.pembayaran.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="jenis_pembayaran" value="{{ $jenis }}">
                <input type="hidden" name="nominal" value="{{ $nominal }}">
                @if($booking)
                    <input type="hidden" name="booking_id" value="{{ $booking->id }}">
                @endif
                @if($tagihan)
                    <input type="hidden" name="tagihan_id" value="{{ $tagihan->id }}">
                @endif
                @if($perpanjangan)
                    <input type="hidden" name="perpanjangan_id" value="{{ $perpanjangan->id }}">
                @endif

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">Bank Pengirim</label>
                        <input type="text" name="bank_pengirim" class="form-control" placeholder="Contoh: BCA / Mandiri / BRI" value="{{ old('bank_pengirim') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">Nama Pemilik Rekening Pengirim</label>
                        <input type="text" name="nama_pengirim" class="form-control" placeholder="Sesuai buku tabungan" value="{{ old('nama_pengirim', Auth::user()->name) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">Tanggal Transfer</label>
                        <input type="date" name="tanggal_bayar" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">Nominal yang Ditransfer</label>
                        <input type="text" class="form-control bg-light" value="Rp {{ number_format($nominal, 0, ',', '.') }}" disabled>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Unggah File Bukti Transfer (Foto / Screenshot / PDF)</label>
                    <input type="file" name="bukti_pembayaran" class="form-control" accept="image/*,application/pdf" required>
                    <div class="form-text small text-muted">Format: JPG, PNG, atau PDF. Maksimum ukuran file: 3 MB.</div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-semibold text-secondary">Catatan untuk Pengelola Kost (Opsional)</label>
                    <textarea name="catatan_penghuni" rows="2" class="form-control" placeholder="Contoh: Transfer via m-BCA a.n. orang tua..."></textarea>
                </div>

                <button type="submit" class="btn btn-primary-griya w-100 py-3 fw-semibold">
                    <i class="bi bi-cloud-arrow-up me-2"></i> Kirim Bukti Pembayaran
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
