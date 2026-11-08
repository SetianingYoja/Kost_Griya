@extends('layouts.dashboard')

@section('title', 'Data Penghuni Kost — Pemilik Kost')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <h3 class="fw-bold text-secondary mb-0">Data Penghuni Kost Griya Ayu</h3>
            <span class="badge bg-secondary-subtle text-secondary border px-2 py-1 small"><i class="bi bi-eye me-1"></i>Read-Only</span>
        </div>
        <p class="text-muted small mb-0">Informasi penyewa kamar aktif serta arsip riwayat penghuni lama</p>
    </div>
    <div class="btn-group">
        <a href="{{ route('pemilik-kost.penghuni.index', ['status' => 'aktif']) }}" class="btn btn-sm {{ $status === 'aktif' ? 'btn-primary-griya' : 'btn-light border' }}">
            Penghuni Aktif
        </a>
        <a href="{{ route('pemilik-kost.penghuni.index', ['status' => 'semua']) }}" class="btn btn-sm {{ $status === 'semua' ? 'btn-primary-griya' : 'btn-light border' }}">
            Semua Riwayat Penghuni
        </a>
    </div>
</div>

<div class="card-griya p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nama Penghuni</th>
                    <th>Kontak WhatsApp</th>
                    <th>Email</th>
                    <th>Kamar Disewa</th>
                    <th>Status Sewa</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($penghunis as $penghuni)
                    <tr>
                        <td><strong>{{ $penghuni->name }}</strong></td>
                        <td>
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $penghuni->phone) }}" target="_blank" class="text-success text-decoration-none fw-semibold">
                                <i class="bi bi-whatsapp me-1"></i> {{ $penghuni->phone }}
                            </a>
                        </td>
                        <td>{{ $penghuni->email }}</td>
                        <td>
                            @if($penghuni->activeSewa)
                                <strong class="text-primary">{{ $penghuni->activeSewa->kamar->nomor_kamar ?? '-' }}</strong>
                                <small class="text-muted d-block">s/d {{ $penghuni->activeSewa->tanggal_selesai->format('d/m/Y') }}</small>
                            @else
                                <span class="text-muted">Tidak ada sewa aktif</span>
                            @endif
                        </td>
                        <td>
                            @if($penghuni->activeSewa)
                                <span class="badge bg-success">Aktif Menyewa</span>
                            @else
                                <span class="badge bg-secondary">Alumni / Nonaktif</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('pemilik-kost.penghuni.show', $penghuni->id) }}" class="btn btn-sm btn-outline-griya">
                                <i class="bi bi-eye"></i> Profil & Arsip
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Belum ada data penghuni yang terdaftar.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $penghunis->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
