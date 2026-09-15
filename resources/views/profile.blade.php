@extends('layouts.app')

@section('title', 'Profil Pengguna — Kost Putri Griya Ayu')

@section('content')
<div class="container py-5">
    <div class="row g-4 justify-content-center">
        <div class="col-lg-8">
            <div class="card-griya p-4 p-md-5 mb-4">
                <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom">
                    <div class="stat-icon bg-primary text-white" style="width: 54px; height: 54px;">
                        <i class="bi bi-person-fill fs-3"></i>
                    </div>
                    <div>
                        <h2 class="h4 fw-bold text-secondary mb-0">{{ $user->name }}</h2>
                        <span class="badge bg-primary">{{ $user->role->name ?? 'Pengguna' }}</span>
                        <span class="badge bg-success ms-1">{{ $user->status }}</span>
                    </div>
                </div>

                <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Nama Lengkap</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Alamat Email (Akun)</label>
                            <input type="email" class="form-control bg-light" value="{{ $user->email }}" disabled>
                            <small class="text-muted">Email tidak dapat diubah secara langsung demi keamanan.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Nomor WhatsApp</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Foto Avatar</label>
                            <input type="file" name="avatar" class="form-control" accept="image/*">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary-griya px-4">
                        <i class="bi bi-check-circle me-1"></i> Simpan Perubahan Profil
                    </button>
                </form>
            </div>

            <!-- Ubah Password -->
            <div class="card-griya p-4 p-md-5">
                <h4 class="fw-bold text-secondary mb-3"><i class="bi bi-shield-lock me-2 text-primary"></i>Ubah Kata Sandi</h4>
                <form action="{{ route('profile.password') }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="row g-3 mb-4">
                        <div class="col-md-12">
                            <label class="form-label small fw-semibold text-secondary">Kata Sandi Saat Ini</label>
                            <input type="password" name="current_password" class="form-control" placeholder="••••••••" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Kata Sandi Baru (Min. 8 Karakter)</label>
                            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-secondary">Konfirmasi Kata Sandi Baru</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="••••••••" required>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-secondary-griya px-4">
                        <i class="bi bi-key me-1"></i> Perbarui Kata Sandi
                    </button>
                </form>
            </div>
        </div>

        <!-- Riwayat Aktivitas Akun -->
        <div class="col-lg-4">
            <div class="card-griya p-4">
                <h5 class="fw-bold text-secondary mb-3"><i class="bi bi-clock-history me-2 text-primary"></i>Aktivitas Terakhir</h5>
                <div class="list-group list-group-flush small">
                    @forelse($riwayats as $riwayat)
                        <div class="list-group-item px-0 py-2 border-0 border-bottom">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong class="text-secondary">{{ $riwayat->judul }}</strong>
                                <span class="badge bg-light text-muted">{{ $riwayat->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-muted mb-0 small">{{ $riwayat->deskripsi }}</p>
                        </div>
                    @empty
                        <p class="text-muted small mb-0">Belum ada riwayat aktivitas yang tercatat.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
