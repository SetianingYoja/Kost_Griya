@extends('layouts.dashboard')

@section('title', 'User Management')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h3 class="fw-bold text-secondary mb-1">User Management</h3>
        <p class="text-muted small mb-0">Kelola seluruh akun pengguna, penetapan peran (role), dan status aktif/nonaktif</p>
    </div>
    <a href="{{ route('admin.users.create') }}" class="btn btn-primary-griya btn-sm">
        <i class="bi bi-person-plus me-1"></i> Tambah User Baru
    </a>
</div>

<!-- Filter Bar -->
<div class="card-griya p-3 mb-4">
    <form action="{{ route('admin.users.index') }}" method="GET" class="row g-2 align-items-center">
        <div class="col-md-5">
            <input type="text" name="q" class="form-control form-control-sm" placeholder="Cari nama, email, atau WhatsApp..." value="{{ request('q') }}">
        </div>
        <div class="col-md-3">
            <select name="role_id" class="form-select form-select-sm">
                <option value="">Semua Peran (Role)</option>
                @foreach($roles as $r)
                    <option value="{{ $r->id }}" {{ request('role_id') == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <select name="status" class="form-select form-select-sm">
                <option value="">Semua Status</option>
                <option value="Aktif" {{ request('status') == 'Aktif' ? 'selected' : '' }}>Aktif</option>
                <option value="Nonaktif" {{ request('status') == 'Nonaktif' ? 'selected' : '' }}>Nonaktif</option>
            </select>
        </div>
        <div class="col-md-2 d-flex gap-1">
            <button type="submit" class="btn btn-sm btn-primary-griya flex-grow-1">Filter</button>
            <a href="{{ route('admin.users.index') }}" class="btn btn-sm btn-light border"><i class="bi bi-arrow-counterclockwise"></i></a>
        </div>
    </form>
</div>

<div class="card-griya p-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nama Pengguna</th>
                    <th>Email</th>
                    <th>No. WhatsApp</th>
                    <th>Peran (Role)</th>
                    <th>Status Akun</th>
                    <th>Terdaftar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $u)
                    <tr>
                        <td><strong>{{ $u->name }}</strong></td>
                        <td>{{ $u->email }}</td>
                        <td>{{ $u->phone ?: '-' }}</td>
                        <td>
                            @if($u->role && $u->role->slug === 'super-admin')
                                <span class="badge bg-danger">Super Admin</span>
                            @elseif($u->role && $u->role->slug === 'pemilik-kost')
                                <span class="badge bg-primary">Pemilik Kost</span>
                            @else
                                <span class="badge bg-info text-dark">Penghuni</span>
                            @endif
                        </td>
                        <td>
                            <form action="{{ route('admin.users.toggle', $u->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="btn btn-sm py-0 px-2 btn-{{ $u->status === 'Aktif' ? 'success' : 'secondary' }}" title="Klik untuk ubah status" {{ $u->id === Auth::id() ? 'disabled' : '' }}>
                                    {{ $u->status }}
                                </button>
                            </form>
                        </td>
                        <td>{{ $u->created_at->format('d/m/Y') }}</td>
                        <td>
                            <div class="d-flex gap-1">
                                <a href="{{ route('admin.users.edit', $u->id) }}" class="btn btn-sm btn-light border py-0 px-2" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                @if($u->id !== Auth::id())
                                    <form action="{{ route('admin.users.destroy', $u->id) }}" method="POST" onsubmit="return confirm('Hapus pengguna {{ $u->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Belum ada user yang sesuai.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $users->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
