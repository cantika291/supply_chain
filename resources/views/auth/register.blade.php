@extends('layouts.guest')

@section('title', 'Register - Supply Chain Risk Platform')
@section('subtitle', 'Buat akun baru untuk memantau risiko rantai pasok')

@section('content')
    <form method="POST" action="{{ route('register.store') }}">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Nama Lengkap</label>
            <input type="text"
                class="form-control @error('name') is-invalid @enderror"
                id="name" name="name" value="{{ old('name') }}"
                required autofocus>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email"
                class="form-control @error('email') is-invalid @enderror"
                id="email" name="email" value="{{ old('email') }}"
                required>
            @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="role" class="form-label">Daftar Sebagai</label>
            <select class="form-select @error('role') is-invalid @enderror"
                id="role" name="role" required>
                <option value="user" {{ old('role') === 'user' ? 'selected' : '' }}>
                    👤 User — Akses semua dashboard monitoring
                </option>
                <option value="admin" {{ old('role') === 'admin' ? 'selected' : '' }}>
                    🔧 Admin — Akses dashboard + kelola platform
                </option>
            </select>
            @error('role')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password"
                class="form-control @error('password') is-invalid @enderror"
                id="password" name="password" required>
            <div class="form-text">Minimal 8 karakter.</div>
            @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
            <input type="password" class="form-control"
                id="password_confirmation" name="password_confirmation" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Daftar</button>

        <p class="text-center mt-3 mb-0" style="font-size:0.9rem;">
            Sudah punya akun? <a href="{{ route('login') }}">Login di sini</a>
        </p>
    </form>
@endsection