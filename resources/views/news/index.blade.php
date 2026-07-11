@extends('layouts.app')

@section('title', 'News Intelligence')

@section('content')
<div class="row g-4">

    {{-- Header --}}
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="mb-1">News Intelligence</h4>
                <p class="text-muted mb-0">Analisis sentimen berita ekonomi, logistik, dan geopolitik global</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <small class="text-muted" id="lastUpdatedNews"></small>
                <button class="btn btn-outline-info btn-sm text-dark" id="btnRefreshNews">
                    <i class="bi bi-arrow-clockwise me-1"></i>Refresh Berita
                </button>
                <span class="badge bg-primary fs-6">{{ $summary['total'] }} Berita</span>
            </div>
        </div>
    </div>

    {{-- Sentiment Summary Cards --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #198754 !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Positive</p>
                        <h3 class="mb-0 text-success">{{ $summary['positive'] }}</h3>
                        <small class="text-muted">{{ $summary['positive_pct'] }}% dari total</small>
                    </div>
                    <i class="bi bi-emoji-smile-fill text-success" style="font-size: 2.5rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #6c757d !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Neutral</p>
                        <h3 class="mb-0 text-secondary">{{ $summary['neutral'] }}</h3>
                        <small class="text-muted">{{ $summary['neutral_pct'] }}% dari total</small>
                    </div>
                    <i class="bi bi-emoji-neutral-fill text-secondary" style="font-size: 2.5rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #dc3545 !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Negative</p>
                        <h3 class="mb-0 text-danger">{{ $summary['negative'] }}</h3>
                        <small class="text-muted">{{ $summary['negative_pct'] }}% dari total</small>
                    </div>
                    <i class="bi bi-emoji-frown-fill text-danger" style="font-size: 2.5rem; opacity: 0.3;"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Chart Sentimen + Chart Kategori --}}
    <div class="col-md-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="mb-0">Distribusi Sentimen</h6>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <canvas id="sentimentChart" style="max-height: 250px;"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="mb-0">Berita per Kategori</h6>
            </div>
            <div class="card-body d-flex align-items-center">
                <canvas id="categoryChart" style="max-height: 250px; width: 100%;"></canvas>
            </div>
        </div>
    </div>

    {{-- Daftar Berita --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Daftar Berita Terbaru</h6>
                <div class="d-flex gap-2">
                    @foreach(['logistics','trade','shipping','economy','geopolitics'] as $cat)
                        <span class="badge bg-light text-dark border">{{ ucfirst($cat) }}: {{ $categoryStats[$cat] ?? 0 }}</span>
                    @endforeach
                </div>
            </div>
            <div class="card-body p-0">
                @forelse ($news as $article)
                    <div class="d-flex gap-3 p-3 border-bottom align-items-start">
                        {{-- Badge Sentimen --}}
                        <div class="flex-shrink-0 mt-1">
                            @php
                                $sentiment = $article->sentiment?->sentiment ?? 'Neutral';
                                $badgeClass = match($sentiment) {
                                    'Positive' => 'bg-success',
                                    'Negative' => 'bg-danger',
                                    default    => 'bg-secondary',
                                };
                                $icon = match($sentiment) {
                                    'Positive' => 'bi-arrow-up-circle-fill',
                                    'Negative' => 'bi-arrow-down-circle-fill',
                                    default    => 'bi-dash-circle-fill',
                                };
                            @endphp
                            <span class="badge {{ $badgeClass }}">
                                <i class="bi {{ $icon }} me-1"></i>{{ $sentiment }}
                            </span>
                        </div>

                        {{-- Konten Berita --}}
                        <div class="flex-grow-1">
                            <a href="{{ $article->source_url }}"
                               target="_blank"
                               rel="noopener noreferrer"
                               class="text-decoration-none text-dark fw-semibold">
                                {{ $article->title }}
                            </a>
                            <p class="text-muted small mb-1 mt-1">
                                {{ Str::limit($article->description, 120) }}
                            </p>
                            <div class="d-flex gap-2 align-items-center">
                                <span class="badge bg-light text-dark border">{{ ucfirst($article->category ?? '-') }}</span>
                                <small class="text-muted">{{ $article->source_name }}</small>
                                @if ($article->published_at)
                                    <small class="text-muted">· {{ $article->published_at->diffForHumans() }}</small>
                                @endif
                                @if ($article->sentiment)
                                    <small class="text-muted">
                                        · 👍 {{ $article->sentiment->positive_score }}
                                        👎 {{ $article->sentiment->negative_score }}
                                    </small>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-newspaper" style="font-size: 2rem;"></i>
                        <p class="mt-2">Belum ada berita. Jalankan <code>php artisan sync:countries</code> dulu.</p>
                    </div>
                @endforelse
            </div>

            @if ($news->hasPages())
                <div class="card-footer bg-white border-0">
                    {{ $news->links() }}
                </div>
            @endif
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
    // Chart 1: Pie chart distribusi sentimen
    new Chart(document.getElementById('sentimentChart'), {
        type: 'doughnut',
        data: {
            labels: ['Positive', 'Neutral', 'Negative'],
            datasets: [{
                data: [
                    {{ $summary['positive'] }},
                    {{ $summary['neutral'] }},
                    {{ $summary['negative'] }}
                ],
                backgroundColor: ['#198754', '#6c757d', '#dc3545'],
                borderWidth: 0,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // Chart 2: Bar chart berita per kategori
    new Chart(document.getElementById('categoryChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_map('ucfirst', array_keys($categoryStats))) !!},
            datasets: [{
                label: 'Jumlah Berita',
                data: {!! json_encode(array_values($categoryStats)) !!},
                backgroundColor: ['#0d6efd','#6610f2','#0dcaf0','#ffc107','#fd7e14'],
                borderRadius: 6,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });

    const lnu = localStorage.getItem('last_news_update');
if (lnu) {
    const diff = Math.round((Date.now() - parseInt(lnu)) / 60000);
    document.getElementById('lastUpdatedNews').textContent =
        `Update: ${diff < 1 ? 'baru saja' : diff + ' mnt lalu'}`;
}

document.getElementById('btnRefreshNews')?.addEventListener('click', function() {
    if (!confirm('Refresh berita dari GNews API? Ini menggunakan kuota harian.')) return;
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Fetching...';
    fetch('/refresh/news', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        }
    })
    .then(r => r.json())
    .then(d => {
        localStorage.setItem('last_news_update', Date.now());
        alert('✅ ' + d.message);
        window.location.reload();
    })
    .catch(() => alert('Gagal refresh.'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i>Refresh Berita';
    });
});
</script>
@endpush