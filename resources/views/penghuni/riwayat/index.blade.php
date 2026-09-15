@extends('layouts.dashboard')

@section('title', 'Riwayat & Arsip Lengkap')

@section('content')
<div class="mb-4">
    <h3 class="fw-bold text-secondary mb-1">Arsip Riwayat Penghuni</h3>
    <p class="text-muted small mb-0">Seluruh rekaman sewa, transaksi, dan interaksi Anda tersimpan permanen di akun ini</p>
</div>

<!-- Nav Tabs -->
<ul class="nav nav-pills mb-4 gap-2">
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'sewa' ? 'active bg-primary' : 'bg-white border text-secondary' }}" href="{{ route('penghuni.riwayat.index', ['tab' => 'sewa']) }}">
            <i class="bi bi-door-open me-1"></i> Kontrak Sewa ({{ $sewas->count() }})
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'booking' ? 'active bg-primary' : 'bg-white border text-secondary' }}" href="{{ route('penghuni.riwayat.index', ['tab' => 'booking']) }}">
            <i class="bi bi-calendar-check me-1"></i> Booking ({{ $bookings->count() }})
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'pembayaran' ? 'active bg-primary' : 'bg-white border text-secondary' }}" href="{{ route('penghuni.riwayat.index', ['tab' => 'pembayaran']) }}">
            <i class="bi bi-credit-card me-1"></i> Pembayaran ({{ $pembayarans->count() }})
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'tagihan' ? 'active bg-primary' : 'bg-white border text-secondary' }}" href="{{ route('penghuni.riwayat.index', ['tab' => 'tagihan']) }}">
            <i class="bi bi-receipt me-1"></i> Tagihan ({{ $tagihans->count() }})
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link {{ $tab === 'keluhan' ? 'active bg-primary' : 'bg-white border text-secondary' }}" href="{{ route('penghuni.riwayat.index', ['tab' => 'keluhan']) }}">
            <i class="bi bi-exclamation-octagon me-1"></i> Keluhan ({{ $keluhans->count() }})
        </a>
    </li>
</ul>

<div class="card-griya p-4 mb-4">
    <form method="GET" action="{{ route('penghuni.riwayat.index') }}" class="row g-3 align-items-end">
        <input type="hidden" name="tab" value="{{ $tab }}">
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
            <a href="{{ route('penghuni.riwayat.index', ['tab' => $tab]) }}" class="btn btn-outline-secondary btn-sm flex-fill">Reset</a>
        </div>
    </form>
</div>

<div class="card-griya p-4">
    @if($tab === 'sewa')
        <h5 class="fw-bold text-secondary mb-3">Riwayat Kontrak Sewa Kamar</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle small mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Kamar</th>
                        <th>Tanggal Mulai</th>
                        <th>Tanggal Berakhir</th>
                        <th>Tarif Sewa</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sewas as $sewa)
                        <tr>
                            <td><strong>{{ $sewa->kamar->nomor_kamar }}</strong> ({{ $sewa->kamar->tipeKamar->nama_tipe ?? '' }})</td>
                            <td>{{ $sewa->tanggal_mulai->format('d M Y') }}</td>
                            <td>{{ $sewa->tanggal_selesai->format('d M Y') }}</td>
                            <td>Rp {{ number_format($sewa->harga_per_bulan, 0, ',', '.') }}/bln</td>
                            <td>
                                <span class="badge bg-{{ $sewa->status === 'Aktif' ? 'success' : 'secondary' }}">{{ $sewa->status }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-3 text-muted">Belum ada riwayat sewa.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    @elseif($tab === 'booking')
        <h5 class="fw-bold text-secondary mb-3">Riwayat Seluruh Pemesanan Kamar</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle small mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Kode Booking</th>
                        <th>Kamar</th>
                        <th>Tanggal Mulai</th>
                        <th>Durasi</th>
                        <th>Total Harga</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($bookings as $booking)
                        <tr>
                            <td><strong>{{ $booking->kode_booking }}</strong></td>
                            <td>{{ $booking->kamar->nomor_kamar ?? '-' }}</td>
                            <td>{{ $booking->tanggal_mulai->format('d M Y') }}</td>
                            <td>{{ $booking->durasi_bulan }} Bulan</td>
                            <td>Rp {{ number_format($booking->total_harga, 0, ',', '.') }}</td>
                            <td><span class="badge bg-secondary">{{ $booking->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-3 text-muted">Belum ada riwayat booking.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    @elseif($tab === 'pembayaran')
        <h5 class="fw-bold text-secondary mb-3">Riwayat Seluruh Transaksi Pembayaran</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle small mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Kode</th>
                        <th>Jenis Pembayaran</th>
                        <th>Tanggal Transfer</th>
                        <th>Nominal</th>
                        <th>Bank Pengirim</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pembayarans as $bayar)
                        <tr>
                            <td><strong>{{ $bayar->kode_pembayaran }}</strong></td>
                            <td>{{ $bayar->jenis_pembayaran }}</td>
                            <td>{{ $bayar->tanggal_bayar ? $bayar->tanggal_bayar->format('d M Y') : '-' }}</td>
                            <td>Rp {{ number_format($bayar->nominal, 0, ',', '.') }}</td>
                            <td>{{ $bayar->bank_pengirim }} a.n. {{ $bayar->nama_pengirim }}</td>
                            <td><span class="badge bg-{{ $bayar->status === 'Lunas' ? 'success' : ($bayar->status === 'Ditolak' ? 'danger' : 'warning') }}">{{ $bayar->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-3 text-muted">Belum ada riwayat pembayaran.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    @elseif($tab === 'tagihan')
        <h5 class="fw-bold text-secondary mb-3">Riwayat Seluruh Tagihan Sewa</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle small mb-0">
                <thead class="table-light">
                    <tr>
                        <th>No. Tagihan</th>
                        <th>Periode</th>
                        <th>Kamar</th>
                        <th>Jatuh Tempo</th>
                        <th>Total Bayar</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tagihans as $tagihan)
                        <tr>
                            <td><strong>{{ $tagihan->nomor_tagihan }}</strong></td>
                            <td>{{ $tagihan->periode }}</td>
                            <td>{{ $tagihan->kamar->nomor_kamar ?? '-' }}</td>
                            <td>{{ $tagihan->tanggal_jatuh_tempo->format('d M Y') }}</td>
                            <td>Rp {{ number_format($tagihan->total_bayar, 0, ',', '.') }}</td>
                            <td><span class="badge bg-{{ $tagihan->status === 'Lunas' ? 'success' : 'danger' }}">{{ $tagihan->status }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-3 text-muted">Belum ada riwayat tagihan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    @elseif($tab === 'keluhan')
        <h5 class="fw-bold text-secondary mb-3">Riwayat Seluruh Laporan Keluhan</h5>
        <div class="table-responsive">
            <table class="table table-hover align-middle small mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Kode</th>
                        <th>Judul</th>
                        <th>Tanggal Lapor</th>
                        <th>Status</th>
                        <th>Tanggapan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($keluhans as $klh)
                        <tr>
                            <td><strong>{{ $klh->kode_keluhan }}</strong></td>
                            <td>{{ $klh->judul }}</td>
                            <td>{{ $klh->created_at->format('d M Y') }}</td>
                            <td><span class="badge bg-{{ $klh->status === 'Selesai' ? 'success' : 'info' }}">{{ $klh->status }}</span></td>
                            <td>{{ Str::limit($klh->tanggapan ?: 'Belum ditanggapi', 40) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-center py-3 text-muted">Belum ada riwayat keluhan.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    @endif
</div>
@endsection
