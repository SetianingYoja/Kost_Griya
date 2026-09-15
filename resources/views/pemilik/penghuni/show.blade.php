@extends('layouts.dashboard')

@section('title', 'Profil Penghuni ' . $penghuni->name)

@section('content')
<div class="mb-4">
    <a href="{{ route('pemilik.penghuni.index') }}" class="text-decoration-none small text-muted">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Data Penghuni
    </a>
    <h3 class="fw-bold text-secondary mt-1 mb-0">Profil & Rekam Jejak: {{ $penghuni->name }}</h3>
</div>

<div class="row g-4">
    <!-- Profil Singkat -->
    <div class="col-lg-4">
        <div class="card-griya p-4 mb-4">
            <div class="text-center mb-3">
                <div class="stat-icon mx-auto mb-2 bg-primary text-white" style="width: 60px; height: 60px;">
                    <i class="bi bi-person-fill fs-2"></i>
                </div>
                <h5 class="fw-bold text-secondary mb-0">{{ $penghuni->name }}</h5>
                <span class="badge bg-primary mt-1">{{ $penghuni->role->name ?? 'Penghuni' }}</span>
                <span class="badge bg-success mt-1">{{ $penghuni->status }}</span>
            </div>

            <ul class="list-unstyled small text-secondary mb-4 border-top pt-3">
                <li class="mb-2 d-flex justify-content-between">
                    <span class="text-muted">Email:</span>
                    <strong>{{ $penghuni->email }}</strong>
                </li>
                <li class="mb-2 d-flex justify-content-between">
                    <span class="text-muted">WhatsApp:</span>
                    <strong>{{ $penghuni->phone }}</strong>
                </li>
                <li class="d-flex justify-content-between">
                    <span class="text-muted">Terdaftar Sejak:</span>
                    <span>{{ $penghuni->created_at->format('d M Y') }}</span>
                </li>
            </ul>

            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $penghuni->phone) }}" target="_blank" class="btn btn-success w-100 btn-sm">
                <i class="bi bi-whatsapp me-1"></i> Hubungi via WhatsApp
            </a>
        </div>
    </div>

    <!-- Riwayat Sewa, Tagihan, Keluhan -->
    <div class="col-lg-8">
        <!-- Riwayat Kontrak Sewa -->
        <div class="card-griya p-4 mb-4">
            <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-door-open text-primary me-2"></i>Kontrak Sewa Kamar</h5>
            <div class="table-responsive">
                <table class="table table-sm align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Kamar</th>
                            <th>Mulai</th>
                            <th>Selesai</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($penghuni->sewas as $sewa)
                            <tr>
                                <td><strong>{{ $sewa->kamar->nomor_kamar ?? '-' }}</strong></td>
                                <td>{{ $sewa->tanggal_mulai->format('d M Y') }}</td>
                                <td>{{ $sewa->tanggal_selesai->format('d M Y') }}</td>
                                <td><span class="badge bg-{{ $sewa->status === 'Aktif' ? 'success' : 'secondary' }}">{{ $sewa->status }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-2 text-muted">Belum ada kontrak sewa.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Riwayat Tagihan -->
        <div class="card-griya p-4 mb-4">
            <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-receipt text-primary me-2"></i>Riwayat Tagihan & Pembayaran</h5>
            <div class="table-responsive">
                <table class="table table-sm align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nomor</th>
                            <th>Periode</th>
                            <th>Total</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($penghuni->tagihans as $tagihan)
                            <tr>
                                <td>{{ $tagihan->nomor_tagihan }}</td>
                                <td>{{ $tagihan->periode }}</td>
                                <td>Rp {{ number_format($tagihan->total_bayar, 0, ',', '.') }}</td>
                                <td><span class="badge bg-{{ $tagihan->status === 'Lunas' ? 'success' : 'danger' }}">{{ $tagihan->status }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-2 text-muted">Belum ada data tagihan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Riwayat Keluhan -->
        <div class="card-griya p-4">
            <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-exclamation-octagon text-primary me-2"></i>Laporan Keluhan Fasilitas</h5>
            <div class="table-responsive">
                <table class="table table-sm align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Judul</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($penghuni->keluhans as $klh)
                            <tr>
                                <td><strong>{{ $klh->judul }}</strong></td>
                                <td>{{ $klh->created_at->format('d/m/Y') }}</td>
                                <td><span class="badge bg-{{ $klh->status === 'Selesai' ? 'success' : 'info' }}">{{ $klh->status }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center py-2 text-muted">Tidak ada riwayat keluhan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
