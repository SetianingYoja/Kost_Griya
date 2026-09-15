@extends('layouts.dashboard')

@section('title', 'Role & Permission Management')

@section('content')
<div class="mb-4">
    <h3 class="fw-bold text-secondary mb-1">Role & Permission Management</h3>
    <p class="text-muted small mb-0">Konfigurasi matriks hak akses per modul sesuai struktur RBAC sistem Kost Griya Ayu</p>
</div>

<div class="row g-4">
    @foreach($roles as $role)
        <div class="col-lg-6">
            <div class="card-griya p-4 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                    <div>
                        <h4 class="fw-bold text-secondary mb-0">{{ $role->name }}</h4>
                        <small class="text-muted">{{ $role->description }}</small>
                    </div>
                    <span class="badge bg-{{ $role->slug === 'super-admin' ? 'danger' : ($role->slug === 'pemilik-kost' ? 'primary' : 'info text-dark') }} fs-6">
                        {{ $role->slug }}
                    </span>
                </div>

                @if($role->slug === 'super-admin')
                    <div class="alert alert-info small mb-0">
                        <i class="bi bi-shield-lock-fill me-1"></i> Role Super Administrator memiliki hak akses penuh ke seluruh modul sistem secara permanen.
                    </div>
                @else
                    <form action="{{ route('admin.roles.update', $role->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3" style="max-height: 400px; overflow-y: auto;">
                            @foreach($permissions as $module => $modulePermissions)
                                <div class="mb-3">
                                    <strong class="text-secondary small d-block mb-1 border-bottom pb-1">{{ $module }}</strong>
                                    <div class="row g-2">
                                        @foreach($modulePermissions as $perm)
                                            <div class="col-sm-6">
                                                <div class="form-check small">
                                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $perm->id }}" id="perm_{{ $role->id }}_{{ $perm->id }}" {{ $role->permissions->contains('id', $perm->id) ? 'checked' : '' }}>
                                                    <label class="form-check-label text-muted" for="perm_{{ $role->id }}_{{ $perm->id }}">
                                                        {{ $perm->name }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <button type="submit" class="btn btn-primary-griya btn-sm w-100">
                            <i class="bi bi-check2-circle me-1"></i> Simpan Hak Akses {{ $role->name }}
                        </button>
                    </form>
                @endif
            </div>
        </div>
    @endforeach
</div>
@endsection
