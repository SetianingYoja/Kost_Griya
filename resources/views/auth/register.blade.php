@extends('layouts.app')

@section('title', 'Daftar Akun Baru — Kost Putri Griya Ayu')

@section('content')
<div class="container py-5 my-md-4">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card-griya p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="stat-icon mx-auto mb-3" style="background: rgba(37, 99, 235, 0.1); color: var(--brand-primary); width: 56px; height: 56px;">
                        <i class="bi bi-person-plus-fill fs-3"></i>
                    </div>
                    <h2 class="h3 fw-bold mb-1">Pendaftaran Calon Penghuni</h2>
                    <p class="text-muted small">Buat akun untuk memesan kamar dan menikmati fasilitas Kost Griya Ayu</p>
                </div>

                <form action="{{ route('register.post') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label fw-semibold small text-secondary">Nama Lengkap</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person"></i></span>
                            <input type="text" name="name" id="name" class="form-control bg-light border-start-0 @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Contoh: Anisa Maharani" required autofocus>
                        </div>
                        @error('name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold small text-secondary">Alamat Email Aktif</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" id="email" class="form-control bg-light border-start-0 @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="nama@email.com" required>
                        </div>
                        @error('email')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label fw-semibold small text-secondary">Nomor WhatsApp Aktif</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-whatsapp"></i></span>
                            <input type="text" name="phone" id="phone" class="form-control bg-light border-start-0 @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx" required>
                        </div>
                        <div class="form-text small text-muted">Digunakan untuk konfirmasi booking dan pemberitahuan operasional kost.</div>
                        @error('phone')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="password" class="form-label fw-semibold small text-secondary">Kata Sandi (Min. 8 Karakter)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-key"></i></span>
                                <input type="password" name="password" id="password" class="form-control bg-light border-start-0 @error('password') is-invalid @enderror" placeholder="••••••••" required>
                            </div>
                            @error('password')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label fw-semibold small text-secondary">Ulangi Kata Sandi</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-key-fill"></i></span>
                                <input type="password" name="password_confirmation" id="password_confirmation" class="form-control bg-light border-start-0" placeholder="••••••••" required>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-light border small text-muted mb-4">
                        <i class="bi bi-info-circle text-primary me-1"></i>
                        Dengan mendaftar, akun Anda secara otomatis berstatus sebagai <strong>Penghuni</strong> dan tunduk pada tata tertib Kost Putri Griya Ayu.
                    </div>

                    <button type="submit" class="btn btn-primary-griya w-100 py-2 fw-semibold">
                        <i class="bi bi-check-circle me-2"></i> Daftar Sekarang
                    </button>
                </form>

                <div class="text-center mt-4 pt-3 border-top">
                    <p class="text-muted small mb-0">
                        Sudah punya akun?
                        <a href="{{ route('login') }}" class="text-primary fw-semibold text-decoration-none">Masuk di sini</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
