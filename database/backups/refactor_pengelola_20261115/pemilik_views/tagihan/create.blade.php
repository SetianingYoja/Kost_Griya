@extends('layouts.dashboard')

@section('title', 'Terbitkan Tagihan Baru')

@section('content')
<div class="mb-4">
    <a href="{{ route('pemilik.tagihan.index') }}" class="text-decoration-none small text-muted">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Tagihan
    </a>
    <h3 class="fw-bold text-secondary mt-1 mb-0">Terbitkan Tagihan Sewa Baru</h3>
</div>

<div class="row g-4 justify-content-center">
    <div class="col-lg-8">
        <div class="card-griya p-4 p-md-5">
            <form action="{{ route('pemilik.tagihan.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Pilih Penyewa Aktif & Kamar</label>
                    <select name="sewa_id" class="form-select" required>
                        <option value="">-- Pilih Penyewa --</option>
                        @foreach($activeSewas as $sewa)
                            <option value="{{ $sewa->id }}">
                                {{ $sewa->user->name }} — {{ $sewa->kamar->nomor_kamar }} (Rp {{ number_format($sewa->harga_per_bulan, 0, ',', '.') }}/bln)
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">Periode Tagihan</label>
                        <input type="text" name="periode" class="form-control" placeholder="Contoh: November 2026 / Bulan Ke-2" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">Tanggal Jatuh Tempo</label>
                        <input type="date" name="tanggal_jatuh_tempo" class="form-control" value="{{ date('Y-m-d', strtotime('+7 days')) }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">Nominal Tagihan (Rp)</label>
                        <input type="number" name="nominal" class="form-control" placeholder="Nominal sewa" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">Potongan DP Perpanjangan (Rp, Opsional)</label>
                        <input type="number" name="potongan_dp" class="form-control" value="0">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary-griya w-100 py-3 fw-semibold">
                    <i class="bi bi-send me-2"></i> Terbitkan & Kirim Tagihan
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
