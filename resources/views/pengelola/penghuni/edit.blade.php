@extends('layouts.dashboard')

@section('content')
<div class="container">
    <h1>Edit Penghuni</h1>
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form method="POST" action="{{ route('pengelola.penghuni.update', $penghuni->id) }}">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="name" class="form-label">Nama</label>
            <input type="text" class="form-control" id="name" name="name" value="{{ old('name', $penghuni->name) }}" required>
        </div>
        <div class="mb-3">
            <label for="phone" class="form-label">Phone / WhatsApp</label>
            <input type="text" class="form-control" id="phone" name="phone" value="{{ old('phone', $penghuni->phone) }}" required inputmode="numeric" maxlength="12">
        </div>
        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" class="form-control" id="email" name="email" value="{{ old('email', $penghuni->email) }}" required>
        </div>
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        <a href="{{ route('pengelola.penghuni.show', $penghuni->id) }}" class="btn btn-secondary">Batal</a>
    </form>
</div>
@endsection
