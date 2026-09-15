@extends('layouts.dashboard')

@section('title', 'Edit Pengguna ' . $user->name)

@section('content')
<div class="mb-4">
    <a href="{{ route('admin.users.index') }}" class="text-decoration-none small text-muted">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke User Management
    </a>
    <h3 class="fw-bold text-secondary mt-1 mb-0">Edit Pengguna: {{ $user->name }}</h3>
</div>

<div class="row g-4 justify-content-center">
    <div class="col-lg-8">
        <div class="card-griya p-4 p-md-5">
            <form action="{{ route('admin.users.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">Alamat Email</label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">Nomor WhatsApp</label>
                        <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $user->phone) }}" required>
                        @error('phone')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">Peran (Role)</label>
                        <select name="role_id" class="form-select" required>
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ old('role_id', $user->role_id) == $role->id ? 'selected' : '' }}>
                                    {{ $role->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">Status Akun</label>
                        <select name="status" class="form-select" required>
                            <option value="Aktif" {{ old('status', $user->status) == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                            <option value="Nonaktif" {{ old('status', $user->status) == 'Nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-semibold text-secondary">Ubah Kata Sandi (Kosongkan jika tidak diganti)</label>
                        <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="••••••••">
                        @error('password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <button type="submit" class="btn btn-primary-griya w-100 py-3 fw-semibold">
                    <i class="bi bi-check-circle me-2"></i> Perbarui Data Pengguna
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
