@extends('layouts.app')
@section('title', 'Kelola Artikel')
@section('content')
<div class="row g-4">
    <div class="col-12 d-flex justify-content-between align-items-center">
        <h4 class="mb-0">Kelola Artikel Analisis</h4>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i>Kembali
        </a>
    </div>

    {{-- Form Tambah Artikel --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="mb-0"><i class="bi bi-plus-circle me-2 text-primary"></i>Tulis Artikel Baru</h6>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.articles.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Judul Artikel</label>
                            <input type="text" name="title" class="form-control" required
                                placeholder="Contoh: Analisis Risiko Rantai Pasok China Q3 2026">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Negara Terkait</label>
                            <select name="country_id" class="form-select">
                                <option value="">-- Opsional --</option>
                                @foreach ($countries as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Konten Artikel</label>
                            <textarea name="content" class="form-control" rows="5" required
                                placeholder="Tulis analisis supply chain risk..."></textarea>
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send me-2"></i>Publikasikan Artikel
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Daftar Artikel --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="mb-0">Semua Artikel ({{ $articles->total() }})</h6>
            </div>
            <div class="card-body p-0">
                @forelse ($articles as $article)
                    <div class="d-flex justify-content-between align-items-start p-3 border-bottom">
                        <div class="flex-grow-1">
                            <h6 class="mb-1">{{ $article->title }}</h6>
                            <p class="text-muted small mb-1">{{ Str::limit($article->content, 150) }}</p>
                            <div class="d-flex gap-2">
                                <small class="text-muted">
                                    <i class="bi bi-person me-1"></i>{{ $article->author?->name }}
                                </small>
                                @if ($article->country)
                                    <small class="text-muted">
                                        <i class="bi bi-globe me-1"></i>{{ $article->country->name }}
                                    </small>
                                @endif
                                <small class="text-muted">
                                    <i class="bi bi-clock me-1"></i>{{ $article->created_at->diffForHumans() }}
                                </small>
                            </div>
                        </div>
                        <form method="POST" action="{{ route('admin.articles.destroy', $article) }}" class="ms-3">
                            @csrf @method('DELETE')
                            <button class="btn btn-outline-danger btn-sm"
                                onclick="return confirm('Hapus artikel ini?')">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                    </div>
                @empty
                    <div class="text-center py-4 text-muted">Belum ada artikel.</div>
                @endforelse
            </div>
            @if ($articles->hasPages())
                <div class="card-footer bg-white border-0">{{ $articles->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection