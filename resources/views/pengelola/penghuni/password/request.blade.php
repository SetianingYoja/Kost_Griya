@extends('layouts.dashboard')

@section('content')
<div class="container">
    <h1>Reset Password Penghuni</h1>
    @if (session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
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
    <p>Kirim tautan reset password ke email: <strong>{{ $penghuni->email }}</strong></p>
    <form method="POST" action="{{ route('pengelola.penghuni.password.email', $penghuni->id) }}">
        @csrf
        <input type="hidden" name="email" value="{{ $penghuni->email }}">
        <button type="submit" class="btn btn-primary">Kirim Tautan Reset</button>
        <a href="{{ route('pengelola.penghuni.show', $penghuni->id) }}" class="btn btn-secondary">Kembali</a>
    </form>
</div>
@endsection
