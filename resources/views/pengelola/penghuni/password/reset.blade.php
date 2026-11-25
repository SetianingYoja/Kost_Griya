@extends('layouts.dashboard')

@section('content')
<div class="container">
    <h1>Reset Password Penghuni</h1>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form method="POST" action="{{ route('pengelola.penghuni.password.update', $penghuni->id) }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <input type="hidden" name="email" value="{{ $penghuni->email }}">
        <div class="mb-3">
            <label for="password" class="form-label">Password Baru</label>
            <input type="password" class="form-control" id="password" name="password" required minlength="8">
        </div>
        <div class="mb-3">
            <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
            <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required minlength="8">
        </div>
        <button type="submit" class="btn btn-primary">Reset Password</button>
        <a href="{{ route('pengelola.penghuni.show', $penghuni->id) }}" class="btn btn-secondary ms-2">Batal</a>
    </form>
</div>
@endsection