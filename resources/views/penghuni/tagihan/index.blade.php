@extends('layouts.dashboard')

@section('title', 'Tagihan Sewa Saya')

@section('content')
<div class="mb-4">
    <h3 class="fw-bold text-secondary mb-1">Tagihan Sewa Kamar</h3>
    <p class="text-muted small mb-0">Daftar kewajiban pembayaran sewa bulanan dan perpanjangan Anda</p>
</div>

<div class="card-griya p-4 mb-4">
    <form method="GET" action="{{ route('penghuni.tagihan.index') }}" class="row g-3 align-items-end">
        <div class="col-md-4">
            <label class="form-label small fw-semibold text-secondary">Dari Tanggal</label>
            <input type="date" name="from_date" class="form-control" value="{{ $fromDate ?? '' }}">
        </div>
        <div class="col-md-4">
            <label class="form-label small fw-semibold text-secondary">Sampai Tanggal</label>
            <input type="date" name="to_date" class="form-control" value="{{ $toDate ?? '' }}">
        </div>
        <div class="col-md-4 d-flex gap-2">
            <button type="submit" class="btn btn-primary-griya btn-sm flex-fill">Cari</button>
            <a href="{{ route('penghuni.tagihan.index') }}" class="btn btn-outline-secondary btn-sm flex-fill">Reset</a>
        </div>
    </form>
</div>

<div class="card-griya p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>No. Tagihan</th>
                    <th>Periode</th>
                    <th>Jatuh Tempo</th>
                    <th>Total Tagihan</th>
                    <th>Potongan DP</th>
                    <th>Wajib Bayar</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($tagihans as $tagihan)
                    <tr>
                        <td><strong>{{ $tagihan->nomor_tagihan }}</strong></td>
                        <td>{{ $tagihan->periode }}</td>
                        <td>{{ $tagihan->tanggal_jatuh_tempo->format('d M Y') }}</td>
                        <td>Rp {{ number_format($tagihan->nominal, 0, ',', '.') }}</td>
                        <td>
                            @if($tagihan->potongan_dp > 0)
                                <span class="text-success">- Rp {{ number_format($tagihan->potongan_dp, 0, ',', '.') }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td><strong class="text-primary">Rp {{ number_format($tagihan->total_bayar, 0, ',', '.') }}</strong></td>
                        <td>
                            @if($tagihan->status === 'Lunas')
                                <span class="badge bg-success">Lunas</span>
                            @elseif($tagihan->status === 'Menunggu Validasi')
                                <span class="badge bg-warning text-dark">Validasi</span>
                            @elseif($tagihan->status === 'Terlambat')
                                <span class="badge bg-danger">Terlambat</span>
                            @else
                                <span class="badge bg-danger">Belum Dibayar</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('penghuni.tagihan.show', $tagihan->id) }}" class="btn btn-sm btn-outline-griya">
                                <i class="bi bi-eye"></i> Detail
                            </a>
                            @if(in_array($tagihan->status, ['Belum Dibayar', 'Terlambat']))
                                <a href="{{ route('penghuni.pembayaran.create', ['tagihan_id' => $tagihan->id]) }}" class="btn btn-sm btn-primary-griya ms-1">
                                    <i class="bi bi-upload"></i> Bayar
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">Belum ada tagihan sewa yang diterbitkan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $tagihans->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
