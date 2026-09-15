@extends('layouts.dashboard')

@section('title', 'Tambah Pengguna Baru')

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.users.index') }}" class="text-decoration-none small text-muted">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke User Management
    </a>
    <h3 class="fw-bold text-secondary mt-1 mb-0">Tambah Akun Pengguna Baru</h3>
</div>

<div class="row g-4 justify-content-center">
    <div class="col-lg-8">
        <div class="card-griya p-4 p-md-5">
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">Alamat Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required>
                        @error('email')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">Nomor WhatsApp</label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx" required>
                        @error('phone')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">Peran (Role)</label>
                        <select name="role_id" class="form-select" required>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">Status Akun</label>
                        <select name="status" class="form-select" required>
                            <option value="Aktif" selected>Aktif</option>
                            <option value="Nonaktif">Nonaktif</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">Kata Sandi Awal (Min. 8 Karakter)</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="••••••••" required>
                        @error('password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary-griya w-100 py-3 fw-semibold">
                    <i class="bi bi-person-check me-2"></i> Buat Pengguna Baru
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
