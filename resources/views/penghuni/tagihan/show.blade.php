@extends('layouts.dashboard')

@section('title', 'Detail Tagihan ' . $tagihan->nomor_tagihan)

@section('content')
<div class="mb-4">
    <a href="{{ route('penghuni.tagihan.index') }}" class="text-decoration-none small text-muted">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Daftar Tagihan
    </a>
    <h3 class="fw-bold text-secondary mt-1 mb-0">Invoice Tagihan: {{ $tagihan->nomor_tagihan }}</h3>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card-griya p-4 p-md-5">
            <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
                <div>
                    <h4 class="fw-bold text-secondary mb-1">Kost Putri Griya Ayu</h4>
                    <span class="text-muted small">Periode: {{ $tagihan->periode }}</span>
                </div>
                <div>
                    @if($tagihan->status === 'Lunas')
                        <span class="badge bg-success fs-6 px-3 py-2">Lunas</span>
                    @elseif($tagihan->status === 'Menunggu Validasi')
                        <span class="badge bg-warning text-dark fs-6 px-3 py-2">Menunggu Validasi</span>
                    @else
                        <span class="badge bg-danger fs-6 px-3 py-2">Belum Dibayar</span>
                    @endif
                </div>
            </div>

            <div class="row g-3 small mb-4">
                <div class="col-sm-6">
                    <span class="text-muted d-block">Nomor Kamar:</span>
                    <strong>{{ $tagihan->kamar->nomor_kamar }} ({{ $tagihan->kamar->tipeKamar->nama_tipe ?? '' }})</strong>
                </div>
                <div class="col-sm-6">
                    <span class="text-muted d-block">Tanggal Jatuh Tempo:</span>
                    <strong class="text-danger">{{ $tagihan->tanggal_jatuh_tempo->format('d F Y') }}</strong>
                </div>
            </div>

            <div class="table-responsive mb-4">
                <table class="table table-bordered small">
                    <thead class="table-light">
                        <tr>
                            <th>Deskripsi Tagihan</th>
                            <th class="text-end">Jumlah</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Sewa Kamar Periode {{ $tagihan->periode }}</td>
                            <td class="text-end">Rp {{ number_format($tagihan->nominal, 0, ',', '.') }}</td>
                        </tr>
                        @if($tagihan->potongan_dp > 0)
                            <tr>
                                <td class="text-success">Alokasi Potongan DP Perpanjangan (Model B)</td>
                                <td class="text-end text-success">- Rp {{ number_format($tagihan->potongan_dp, 0, ',', '.') }}</td>
                            </tr>
                        @endif
                        <tr class="table-light fw-bold">
                            <td>Total Wajib Dibayar</td>
                            <td class="text-end text-primary fs-5">Rp {{ number_format($tagihan->total_bayar, 0, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            @if(in_array($tagihan->status, ['Belum Dibayar', 'Terlambat']))
                <a href="{{ route('penghuni.pembayaran.create', ['tagihan_id' => $tagihan->id]) }}" class="btn btn-primary-griya w-100 py-3 fw-semibold">
                    <i class="bi bi-credit-card me-2"></i> Bayar Tagihan Ini Sekarang
                </a>
            @endif
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-griya p-4">
            <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-clock-history text-primary me-2"></i>Riwayat Transaksi</h5>
            @forelse($tagihan->pembayarans as $pembayaran)
                <div class="p-3 bg-light rounded-3 border mb-2 small">
                    <div class="d-flex justify-content-between mb-1">
                        <strong>{{ $pembayaran->kode_pembayaran }}</strong>
                        <span class="badge bg-{{ $pembayaran->status === 'Lunas' ? 'success' : 'warning' }}">{{ $pembayaran->status }}</span>
                    </div>
                    <div class="text-muted">Transfer: Rp {{ number_format($pembayaran->nominal, 0, ',', '.') }}</div>
                    <div class="text-muted">{{ $pembayaran->tanggal_bayar ? $pembayaran->tanggal_bayar->format('d M Y') : '' }}</div>
                </div>
            @empty
                <p class="text-muted small mb-0">Belum ada pembayaran yang dicatat untuk invoice ini.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection
