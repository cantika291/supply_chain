@extends('layouts.app')
@section('title', 'Admin Panel')
@section('content')
<div class="row g-4">

    <div class="col-12">
        <h4 class="mb-1">Admin Dashboard</h4>
        <p class="text-muted mb-0">Kelola user, pelabuhan, dan artikel analisis platform</p>
    </div>

    {{-- Stats --}}
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <i class="bi bi-people-fill text-primary" style="font-size:2rem;"></i>
            <h3 class="mt-2 mb-0">{{ $stats['users'] }}</h3>
            <p class="text-muted small mb-2">Total User</p>
            <a href="{{ route('admin.users') }}" class="btn btn-outline-primary btn-sm">Kelola User</a>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <i class="bi bi-globe text-success" style="font-size:2rem;"></i>
            <h3 class="mt-2 mb-0">{{ $stats['countries'] }}</h3>
            <p class="text-muted small mb-2">Total Negara</p>
            <a href="{{ route('countries.index') }}" class="btn btn-outline-success btn-sm">Lihat Negara</a>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <i class="bi bi-geo-alt-fill text-warning" style="font-size:2rem;"></i>
            <h3 class="mt-2 mb-0">{{ number_format($stats['ports']) }}</h3>
            <p class="text-muted small mb-2">Total Pelabuhan</p>
            <a href="{{ route('admin.ports') }}" class="btn btn-outline-warning btn-sm">Kelola Pelabuhan</a>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-center p-3">
            <i class="bi bi-file-earmark-text-fill text-info" style="font-size:2rem;"></i>
            <h3 class="mt-2 mb-0">{{ $stats['articles'] }}</h3>
            <p class="text-muted small mb-2">Total Artikel</p>
            <a href="{{ route('admin.articles') }}" class="btn btn-outline-info btn-sm">Kelola Artikel</a>
        </div>
    </div>

    {{-- Recent Users --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0">User Terbaru</h6>
                <a href="{{ route('admin.users') }}" class="btn btn-outline-primary btn-sm">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr><th>Nama</th><th>Email</th><th>Role</th><th>Bergabung</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($recentUsers as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td class="text-muted small">{{ $user->email }}</td>
                                <td>
                                    <span class="badge {{ $user->role === 'admin' ? 'bg-danger' : 'bg-secondary' }}">
                                        {{ $user->role }}
                                    </span>
                                </td>
                                <td class="text-muted small">{{ $user->created_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Recent Articles --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Artikel Terbaru</h6>
                <a href="{{ route('admin.articles') }}" class="btn btn-outline-primary btn-sm">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                @forelse ($articles as $article)
                    <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                        <div>
                            <p class="mb-0 small fw-semibold">{{ Str::limit($article->title, 50) }}</p>
                            <small class="text-muted">
                                {{ $article->author?->name }} · {{ $article->created_at->diffForHumans() }}
                            </small>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-3 text-muted small">Belum ada artikel.</div>
                @endforelse
            </div>
        </div>
    </div>

</div>
@endsection