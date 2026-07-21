@extends('layouts.app')
@section('title', 'News Intelligence')
@section('content')
<div class="row g-4">

    {{-- Header --}}
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="mb-1">News Intelligence</h4>
                <p class="text-muted mb-0">Analisis sentimen & dampak berita terhadap rantai pasok global</p>
                @if($lastSync)
                    <small class="text-muted">
                        <i class="bi bi-clock me-1"></i>Terakhir diperbarui: <strong>{{ $lastSync }}</strong>
                    </small>
                @endif
            </div>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-outline-info btn-sm" id="btnRefreshNews">
                    <i class="bi bi-arrow-clockwise me-1"></i>Refresh Berita
                </button>
                <span class="badge bg-primary fs-6">{{ $summary['total'] }} Berita</span>
            </div>
        </div>
    </div>

    {{-- Filter Bar --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-2">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small text-muted mb-1">Filter Negara</label>
                        <select id="filterCountry" class="form-select form-select-sm">
                            <option value="">-- Semua Negara --</option>
                            @foreach ($countries as $c)
                                <option value="{{ $c->cca3 }}">{{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted mb-1">Filter Kategori</label>
                        <select id="filterCategory" class="form-select form-select-sm">
                            <option value="">-- Semua Kategori --</option>
                            <option value="logistics">Logistics</option>
                            <option value="trade">Trade</option>
                            <option value="shipping">Shipping</option>
                            <option value="economy">Economy</option>
                            <option value="geopolitics">Geopolitics</option>
                        </select>
                    </div>
                    <div class="col-md-auto">
                        <button id="btnFilter" class="btn btn-primary btn-sm">
                            <i class="bi bi-funnel me-1"></i>Filter
                        </button>
                        <button id="btnClearFilter" class="btn btn-outline-secondary btn-sm ms-1">
                            Reset
                        </button>
                    </div>
                    <div class="col-md-auto ms-auto">
                        <small class="text-muted" id="filterInfo">Menampilkan semua berita</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sentiment Summary --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #198754 !important;">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted small mb-1">Positive</p>
                    <h3 class="mb-0 text-success">{{ $summary['positive'] }}</h3>
                    <small class="text-muted">{{ $summary['positive_pct'] }}% dari total</small>
                </div>
                <i class="bi bi-emoji-smile-fill text-success" style="font-size:2.5rem;opacity:0.3;"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #6c757d !important;">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted small mb-1">Neutral</p>
                    <h3 class="mb-0 text-secondary">{{ $summary['neutral'] }}</h3>
                    <small class="text-muted">{{ $summary['neutral_pct'] }}% dari total</small>
                </div>
                <i class="bi bi-emoji-neutral-fill text-secondary" style="font-size:2.5rem;opacity:0.3;"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100" style="border-left:4px solid #dc3545 !important;">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted small mb-1">Negative</p>
                    <h3 class="mb-0 text-danger">{{ $summary['negative'] }}</h3>
                    <small class="text-muted">{{ $summary['negative_pct'] }}% dari total</small>
                </div>
                <i class="bi bi-emoji-frown-fill text-danger" style="font-size:2.5rem;opacity:0.3;"></i>
            </div>
        </div>
    </div>

    {{-- Charts --}}
    <div class="col-md-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="mb-0">Distribusi Sentimen Berita</h6>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <canvas id="sentimentChart" style="max-height:230px;"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="mb-0">Berita per Kategori</h6>
            </div>
            <div class="card-body d-flex align-items-center">
                <canvas id="categoryChart" style="max-height:230px;width:100%;"></canvas>
            </div>
        </div>
    </div>

    {{-- Daftar Berita --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                <h6 class="mb-0" id="newsListTitle">Daftar Berita Terbaru</h6>
                <div class="d-flex gap-1 flex-wrap">
                    @foreach(['logistics','trade','shipping','economy','geopolitics'] as $cat)
                        <span class="badge bg-light text-dark border">
                            {{ ucfirst($cat) }}: {{ $categoryStats[$cat] ?? 0 }}
                        </span>
                    @endforeach
                </div>
            </div>

            {{-- Static News (default) --}}
            <div id="staticNewsList">
                <div class="card-body p-0">
                    @forelse ($news as $article)
                        @php
                            $sentiment  = $article->sentiment?->sentiment ?? 'Neutral';
                            $badgeClass = match($sentiment) {
                                'Positive' => 'bg-success',
                                'Negative' => 'bg-danger',
                                default    => 'bg-secondary',
                            };
                        @endphp
                        <div class="d-flex gap-3 p-3 border-bottom align-items-start">
                            {{-- Gambar berita (placeholder berbasis kategori) --}}
                            <div class="flex-shrink-0">
                                <div class="rounded-2 d-flex align-items-center justify-content-center"
                                    style="width:72px;height:72px;background:{{ match($article->category) {
                                        'logistics'   => 'linear-gradient(135deg,#0d6efd22,#0d6efd44)',
                                        'trade'       => 'linear-gradient(135deg,#6610f222,#6610f244)',
                                        'shipping'    => 'linear-gradient(135deg,#0dcaf022,#0dcaf044)',
                                        'economy'     => 'linear-gradient(135deg,#ffc10722,#ffc10744)',
                                        'geopolitics' => 'linear-gradient(135deg,#dc354522,#dc354544)',
                                        default       => 'linear-gradient(135deg,#6c757d22,#6c757d44)',
                                    } }};border-radius:8px;">
                                    <i class="bi {{ match($article->category) {
                                        'logistics'   => 'bi-truck',
                                        'trade'       => 'bi-graph-up-arrow',
                                        'shipping'    => 'bi-water',
                                        'economy'     => 'bi-currency-dollar',
                                        'geopolitics' => 'bi-flag',
                                        default       => 'bi-newspaper',
                                    } }}" style="font-size:1.75rem;color:{{ match($article->category) {
                                        'logistics'   => '#0d6efd',
                                        'trade'       => '#6610f2',
                                        'shipping'    => '#0dcaf0',
                                        'economy'     => '#ffc107',
                                        'geopolitics' => '#dc3545',
                                        default       => '#6c757d',
                                    } }};"></i>
                                </div>
                            </div>

                            <div class="flex-grow-1" style="min-width:0;">
                                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                                    <span class="badge {{ $badgeClass }}">{{ $sentiment }}</span>
                                    <span class="badge bg-light text-dark border small">
                                        {{ ucfirst($article->category ?? '—') }}
                                    </span>
                                    @php
                                        $impact = (new \App\Http\Controllers\NewsController)->supplyChainImpact(
                                            $article->title, $article->description ?? ''
                                        );
                                    @endphp
                                    @if($impact['level'] !== 'None')
                                        <span class="badge bg-{{ $impact['color'] }} bg-opacity-10 text-{{ $impact['color'] }} border border-{{ $impact['color'] }} small">
                                            ⚡ {{ $impact['level'] }} Impact
                                        </span>
                                    @endif
                                </div>

                                <a href="{{ $article->source_url }}"
                                    target="_blank" rel="noopener noreferrer"
                                    class="text-decoration-none text-dark fw-semibold d-block"
                                    style="line-height:1.4;">
                                    {{ $article->title }}
                                </a>

                                <p class="text-muted small mb-1 mt-1">
                                    {{ \Illuminate\Support\Str::limit($article->description, 130) }}
                                </p>

                                {{-- Dampak supply chain --}}
                                @if(!empty($impact['types']))
                                    <div class="d-flex gap-1 flex-wrap mt-1">
                                        @foreach($impact['types'] as $type)
                                            <span class="badge bg-light text-dark border" style="font-size:0.65rem;">
                                                {{ match($type) {
                                                    'logistics'    => '🚛 Logistik',
                                                    'economic'     => '💰 Ekonomi',
                                                    'geopolitical' => '🌐 Geopolitik',
                                                    'weather'      => '⛈️ Cuaca',
                                                    default        => $type,
                                                } }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                <div class="d-flex gap-2 align-items-center mt-1 flex-wrap">
                                    <small class="text-muted fw-semibold">{{ $article->source_name }}</small>
                                    @if($article->published_at)
                                        <small class="text-muted">· {{ $article->published_at->diffForHumans() }}</small>
                                    @endif
                                    @if($article->sentiment)
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
                            <i class="bi bi-newspaper" style="font-size:2rem;"></i>
                            <p class="mt-2">Belum ada berita. Klik Refresh untuk mengambil berita terbaru.</p>
                        </div>
                    @endforelse
                </div>
                @if($news->hasPages())
                    <div class="card-footer bg-white border-0">{{ $news->links() }}</div>
                @endif
            </div>

            {{-- AJAX News (hasil filter) --}}
            <div id="ajaxNewsList" class="d-none">
                <div class="card-body p-0" id="ajaxNewsItems"></div>
                <div class="card-footer bg-white border-0 text-center d-none" id="noResultsMsg">
                    <i class="bi bi-search text-muted" style="font-size:1.5rem;"></i>
                    <p class="text-muted mt-2 mb-0">Tidak ada berita yang cocok dengan filter ini.</p>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
new Chart(document.getElementById('sentimentChart'), {
    type: 'doughnut',
    data: {
        labels: ['Positive', 'Neutral', 'Negative'],
        datasets: [{
            data: [{{ $summary['positive'] }}, {{ $summary['neutral'] }}, {{ $summary['negative'] }}],
            backgroundColor: ['#198754','#6c757d','#dc3545'],
            borderWidth: 0,
        }]
    },
    options: { responsive: true, plugins: { legend: { position: 'bottom' } } }
});

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
        scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
    }
});

// Auto-filter negara aktif
document.addEventListener('DOMContentLoaded', () => {
    const gc = GlobalCountry.get();
    if (gc.cca3) {
        const select = document.getElementById('filterCountry');
        const exists = Array.from(select.options).some(o => o.value === gc.cca3);
        if (exists) { select.value = gc.cca3; doFilter(); }
    }
});

document.getElementById('btnFilter').addEventListener('click', doFilter);
document.getElementById('btnClearFilter').addEventListener('click', clearFilter);
document.getElementById('filterCountry').addEventListener('change', doFilter);
document.getElementById('filterCategory').addEventListener('change', doFilter);

function doFilter() {
    const country  = document.getElementById('filterCountry').value;
    const category = document.getElementById('filterCategory').value;
    if (!country && !category) { clearFilter(); return; }

    let url = '/news/filter?';
    if (country)  url += `country=${encodeURIComponent(country)}&`;
    if (category) url += `category=${encodeURIComponent(category)}`;

    document.getElementById('staticNewsList').classList.add('d-none');
    document.getElementById('ajaxNewsList').classList.remove('d-none');
    document.getElementById('ajaxNewsItems').innerHTML =
        '<div class="text-center py-4"><div class="spinner-border spinner-border-sm text-primary"></div><span class="ms-2 text-muted">Mencari berita...</span></div>';
    document.getElementById('noResultsMsg').classList.add('d-none');

    const countryName = document.getElementById('filterCountry').selectedOptions[0]?.text || '';
    document.getElementById('newsListTitle').textContent =
        `Berita${countryName && countryName !== '-- Semua Negara --' ? ' tentang ' + countryName : ''}`;
    document.getElementById('filterInfo').textContent = 'Mencari...';

    fetch(url, { headers: { 'Accept': 'application/json' } })
    .then(r => r.json())
    .then(data => renderFilteredNews(data))
    .catch(() => {
        document.getElementById('ajaxNewsItems').innerHTML =
            '<div class="text-center py-4 text-danger">Gagal mengambil data.</div>';
    });
}

function renderFilteredNews(data) {
    const container = document.getElementById('ajaxNewsItems');
    const noResults = document.getElementById('noResultsMsg');
    document.getElementById('filterInfo').textContent = `${data.count} berita ditemukan`;

    if (!data.articles.length) {
        container.innerHTML = '';
        noResults.classList.remove('d-none');
        return;
    }

    const categoryIcons = {
        logistics: { icon: 'bi-truck',            color: '#0d6efd', bg: '#0d6efd22' },
        trade:     { icon: 'bi-graph-up-arrow',   color: '#6610f2', bg: '#6610f222' },
        shipping:  { icon: 'bi-water',            color: '#0dcaf0', bg: '#0dcaf022' },
        economy:   { icon: 'bi-currency-dollar',  color: '#ffc107', bg: '#ffc10722' },
        geopolitics:{ icon: 'bi-flag',            color: '#dc3545', bg: '#dc354522' },
    };

    noResults.classList.add('d-none');
    container.innerHTML = data.articles.map(a => {
        const sentiment  = a.sentiment?.label || 'Neutral';
        const badgeClass = sentiment === 'Positive' ? 'bg-success' : sentiment === 'Negative' ? 'bg-danger' : 'bg-secondary';
        const ci         = categoryIcons[a.category] || { icon: 'bi-newspaper', color: '#6c757d', bg: '#6c757d22' };
        const impact     = a.supply_chain_impact || {};
        const impactColor = impact.color || 'secondary';

        const impactTypes = (impact.types || []).map(t => {
            const labels = { logistics:'🚛 Logistik', economic:'💰 Ekonomi', geopolitical:'🌐 Geopolitik', weather:'⛈️ Cuaca' };
            return `<span class="badge bg-light text-dark border" style="font-size:0.65rem;">${labels[t] || t}</span>`;
        }).join(' ');

        return `
        <div class="d-flex gap-3 p-3 border-bottom align-items-start">
            <div class="flex-shrink-0">
                <div class="rounded-2 d-flex align-items-center justify-content-center"
                    style="width:72px;height:72px;background:${ci.bg};border-radius:8px;">
                    <i class="bi ${ci.icon}" style="font-size:1.75rem;color:${ci.color};"></i>
                </div>
            </div>
            <div class="flex-grow-1" style="min-width:0;">
                <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                    <span class="badge ${badgeClass}">${sentiment}</span>
                    <span class="badge bg-light text-dark border small">${a.category || '—'}</span>
                    ${impact.level && impact.level !== 'None'
                        ? `<span class="badge bg-${impactColor} bg-opacity-10 text-${impactColor} border border-${impactColor} small">⚡ ${impact.level} Impact</span>`
                        : ''}
                </div>
                <a href="${a.source_url}" target="_blank" rel="noopener noreferrer"
                    class="text-decoration-none text-dark fw-semibold d-block" style="line-height:1.4;">
                    ${a.title}
                </a>
                <p class="text-muted small mb-1 mt-1">${a.description || ''}</p>
                ${impactTypes ? `<div class="d-flex gap-1 flex-wrap mt-1">${impactTypes}</div>` : ''}
                <div class="d-flex gap-2 align-items-center mt-1">
                    <small class="text-muted fw-semibold">${a.source_name || ''}</small>
                    ${a.published_at ? `<small class="text-muted">· ${a.published_at}</small>` : ''}
                    ${a.sentiment ? `<small class="text-muted">· 👍 ${a.sentiment.positive_score} 👎 ${a.sentiment.negative_score}</small>` : ''}
                </div>
            </div>
        </div>`;
    }).join('');
}

function clearFilter() {
    document.getElementById('filterCountry').value = '';
    document.getElementById('filterCategory').value = '';
    document.getElementById('staticNewsList').classList.remove('d-none');
    document.getElementById('ajaxNewsList').classList.add('d-none');
    document.getElementById('filterInfo').textContent = 'Menampilkan semua berita';
    document.getElementById('newsListTitle').textContent = 'Daftar Berita Terbaru';
}

// Refresh
document.getElementById('btnRefreshNews')?.addEventListener('click', function() {
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Memperbarui...';
    fetch('/news/refresh', {
        method: 'POST',
        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
    })
    .then(r => r.json())
    .then(d => { alert('✅ ' + d.message); window.location.reload(); })
    .catch(() => alert('Gagal refresh.'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i>Refresh Berita';
    });
});
</script>
@endpush