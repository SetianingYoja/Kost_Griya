@extends('layouts.dashboard')

@section('title', 'Data Penghuni Kost')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold text-secondary mb-1">Data Penghuni Kost Griya Ayu</h3>
        <p class="text-muted small mb-0">Informasi seluruh penghuni aktif serta arsip penyewa lama</p>
    </div>
    <div class="btn-group">
        <a href="{{ route('pengelola.penghuni.index', ['status' => 'aktif']) }}" class="btn btn-sm {{ $status === 'aktif' ? 'btn-primary-griya' : 'btn-light border' }}">
            Penghuni Aktif
        </a>
        <a href="{{ route('pengelola.penghuni.index', ['status' => 'semua']) }}" class="btn btn-sm {{ $status === 'semua' ? 'btn-primary-griya' : 'btn-light border' }}">
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
                            <div style="display:inline-flex; gap:5px;">
                                <a href="{{ route('pengelola.penghuni.show', $penghuni->id) }}" class="btn btn-outline-griya" data-bs-toggle="tooltip" title="Profil &amp; Arsip" style="width:32px;height:32px;min-width:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;font-size:14px;">
                                    <i class="bi bi-eye"></i>
                                </a>
                                <a href="{{ route('pengelola.penghuni.edit', $penghuni->id) }}" class="btn btn-outline-primary" data-bs-toggle="tooltip" title="Edit Penghuni" style="width:32px;height:32px;min-width:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;font-size:14px;">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="{{ route('pengelola.penghuni.password.request', $penghuni->id) }}" class="btn btn-outline-warning" data-bs-toggle="tooltip" title="Reset Password" style="width:32px;height:32px;min-width:32px;padding:0;display:inline-flex;align-items:center;justify-content:center;font-size:14px;">
                                    <i class="bi bi-key"></i>
                                </a>
                            </div>
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
