@extends('layouts.guest')

@section('title', 'Login - Supply Chain Risk Platform')
@section('subtitle', 'Masuk untuk mengakses dashboard risiko rantai pasok')

@section('content')
    <form method="POST" action="{{ route('login.store') }}">
        @csrf

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input
                type="email"
                class="form-control @error('email') is-invalid @enderror"
                id="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
            >
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input
                type="password"
                class="form-control @error('password') is-invalid @enderror"
                id="password"
                name="password"
                required
            >
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4 form-check">
            <input type="checkbox" class="form-check-input" id="remember" name="remember">
            <label class="form-check-label" for="remember" style="font-size: 0.9rem;">Ingat saya</label>
        </div>

        <button type="submit" class="btn btn-primary w-100">Masuk</button>

        <p class="text-center mt-3 mb-0" style="font-size: 0.9rem;">
            Belum punya akun? <a href="{{ route('register') }}">Daftar di sini</a>
        </p>
    </form>
@endsection