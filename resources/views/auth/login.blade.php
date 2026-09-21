@extends('layouts.app')
@section('title', 'Masuk ke Akun — Kost Putri Griya Ayu')
@section('content')

<div class="container py-5 my-md-4">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-8">
            <div class="card-griya p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="stat-icon mx-auto mb-3" style="background: rgba(37, 99, 235, 0.1); color: var(--brand-primary); width: 56px; height: 56px;">
                        <i class="bi bi-shield-lock-fill fs-3"></i>
                    </div>
                    <h2 class="h3 fw-bold mb-1"style="color: var(--brand-secondary);">Selamat Datang Kembali </h2>
                    <p class="text-muted small">Silakan masuk ke portal Kost Putri Griya Ayu</p>
                </div>

                <form action="{{ route('login.post') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold small text-secondary">Alamat Email</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-envelope"></i></span>
                            <input type="email" name="email" id="email" class="form-control bg-light border-start-0 @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="nama@email.com" required autofocus>
                        </div>
                        @error('email')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="password" class="form-label fw-semibold small text-secondary mb-0">Kata Sandi (Password)</label>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-key"></i></span>
                            <input type="password" name="password" id="password" class="form-control bg-light border-start-0 @error('password') is-invalid @enderror" placeholder="••••••••" required>
                        </div>
                        @error('password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label small text-secondary" for="remember">
                                Ingat saya di perangkat ini
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary-griya w-100 py-2 fw-semibold">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Masuk Sekarang
                    </button>
                </form>

                <div class="text-center mt-4 pt-3 border-top">
                    <p class="text-muted small mb-0">
                        Belum memiliki akun penghuni?
                        <a href="{{ route('register') }}" class="text-primary fw-semibold text-decoration-none">Daftar di sini</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
