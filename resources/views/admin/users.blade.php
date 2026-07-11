@extends('layouts.app')
@section('title', 'Kelola User')
@section('content')
<div class="row g-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <div>
            <h4 class="mb-1">Kelola User</h4>
            <p class="text-muted mb-0">{{ $users->total() }} user terdaftar</p>
        </div>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>

    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Bergabung</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($users as $user)
                            <tr>
                                <td class="text-muted small">{{ $user->id }}</td>
                                <td>{{ $user->name }}</td>
                                <td class="text-muted small">{{ $user->email }}</td>
                                <td>
                                    <span class="badge {{ $user->role === 'admin' ? 'bg-danger' : 'bg-secondary' }}">
                                        {{ $user->role }}
                                    </span>
                                </td>
                                <td class="text-muted small">{{ $user->created_at->format('d M Y') }}</td>
                                <td class="text-center">
                                    @if ($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.role', $user) }}"
                                            class="d-inline">
                                            @csrf @method('PATCH')
                                            <input type="hidden" name="role"
                                                value="{{ $user->role === 'admin' ? 'user' : 'admin' }}">
                                            <button class="btn btn-outline-warning btn-sm py-0 px-2"
                                                onclick="return confirm('Ubah role {{ $user->name }}?')">
                                                <i class="bi bi-arrow-left-right"></i>
                                                {{ $user->role === 'admin' ? 'Jadikan User' : 'Jadikan Admin' }}
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                            class="d-inline ms-1">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-outline-danger btn-sm py-0 px-2"
                                                onclick="return confirm('Hapus user {{ $user->name }}? Tindakan ini tidak bisa dibatalkan.')">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted small">(Akun kamu)</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if ($users->hasPages())
                <div class="card-footer bg-white border-0">{{ $users->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection