@extends('layouts.app')
@section('title', 'Risk Scoring Engine')
@section('content')
<div class="row g-4">

    {{-- Header --}}
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="mb-1">Risk Scoring Engine</h4>
                <p class="text-muted mb-0">
                    Weighted: Weather (30%) + Inflation (20%) + Currency (10%) + News Sentiment (40%)
                </p>
            </div>
            <div class="d-flex gap-2 align-items-center">
                <button class="btn btn-outline-danger btn-sm" id="btnRefreshRisk">
                    <i class="bi bi-arrow-clockwise me-1"></i>Hitung Ulang Risk Score
                </button>
                <span class="badge bg-primary fs-6">{{ $summary['total'] }} Negara</span>
            </div>
        </div>
    </div>

    {{-- Current Country Risk Card --}}
    <div class="col-12" id="currentCountryRiskCard" style="display:none;">
        <div class="card border-0 shadow-sm" id="currentRiskCardInner"
            style="border-left: 4px solid #6c757d !important;">
            <div class="card-body">
                <div class="row align-items-center g-3">
                    <div class="col-auto">
                        <img id="currentRiskFlag" src="" style="height:40px;border-radius:4px;box-shadow:0 2px 8px rgba(0,0,0,0.15);">
                    </div>
                    <div class="col">
                        <h5 class="mb-0" id="currentRiskCountry">—</h5>
                        <small class="text-muted">Negara yang sedang dipantau</small>
                    </div>
                    <div class="col-auto text-center">
                        <div class="display-6 fw-bold" id="currentRiskScore">—</div>
                        <span class="badge fs-6 px-3 py-2" id="currentRiskLevel">—</span>
                    </div>
                    <div class="col-md-5">
                        <div class="row g-2 text-center">
                            <div class="col-3">
                                <small class="text-muted d-block">Weather</small>
                                <strong id="crWeather">—</strong>
                                <div style="font-size:0.65rem;color:#6c757d;">×30%</div>
                            </div>
                            <div class="col-3">
                                <small class="text-muted d-block">Inflation</small>
                                <strong id="crInflation">—</strong>
                                <div style="font-size:0.65rem;color:#6c757d;">×20%</div>
                            </div>
                            <div class="col-3">
                                <small class="text-muted d-block">Currency</small>
                                <strong id="crCurrency">—</strong>
                                <div style="font-size:0.65rem;color:#6c757d;">×10%</div>
                            </div>
                            <div class="col-3">
                                <small class="text-muted d-block">News</small>
                                <strong id="crNews">—</strong>
                                <div style="font-size:0.65rem;color:#6c757d;">×40%</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm" style="border-left:4px solid #198754 !important;">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted small mb-1">Low Risk</p>
                    <h3 class="mb-0 text-success">{{ $summary['low'] }}</h3>
                    <small class="text-muted">Score 0 – 33</small>
                </div>
                <i class="bi bi-shield-check text-success" style="font-size:2.5rem;opacity:0.3;"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm" style="border-left:4px solid #ffc107 !important;">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted small mb-1">Medium Risk</p>
                    <h3 class="mb-0 text-warning">{{ $summary['medium'] }}</h3>
                    <small class="text-muted">Score 34 – 66</small>
                </div>
                <i class="bi bi-shield-exclamation text-warning" style="font-size:2.5rem;opacity:0.3;"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm" style="border-left:4px solid #dc3545 !important;">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted small mb-1">High Risk</p>
                    <h3 class="mb-0 text-danger">{{ $summary['high'] }}</h3>
                    <small class="text-muted">Score 67 – 100</small>
                </div>
                <i class="bi bi-shield-x text-danger" style="font-size:2.5rem;opacity:0.3;"></i>
            </div>
        </div>
    </div>

    {{-- Chart + Top 5 --}}
    <div class="col-md-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="mb-0">Distribusi Level Risiko Global</h6>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <canvas id="riskDistChart" style="max-height:250px;"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="row g-3 h-100">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 pt-3">
                        <h6 class="mb-0 text-danger">
                            <i class="bi bi-exclamation-triangle me-2"></i>Top 5 Negara Paling Berisiko
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        @foreach ($topRisky as $rs)
                            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                    @if ($rs->country?->flag_url)
                                        <img src="{{ $rs->country->flag_url }}" style="height:16px;border-radius:2px;">
                                    @endif
                                    <span class="small fw-semibold">{{ $rs->country?->name ?? '—' }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress" style="width:80px;height:6px;">
                                        <div class="progress-bar bg-danger" style="width:{{ $rs->total_score }}%"></div>
                                    </div>
                                    <span class="badge bg-danger small">{{ $rs->total_score }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 pt-3">
                        <h6 class="mb-0 text-success">
                            <i class="bi bi-shield-check me-2"></i>Top 5 Negara Paling Aman
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        @foreach ($safest as $rs)
                            <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                    @if ($rs->country?->flag_url)
                                        <img src="{{ $rs->country->flag_url }}" style="height:16px;border-radius:2px;">
                                    @endif
                                    <span class="small fw-semibold">{{ $rs->country?->name ?? '—' }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress" style="width:80px;height:6px;">
                                        <div class="progress-bar bg-success" style="width:{{ $rs->total_score }}%"></div>
                                    </div>
                                    <span class="badge bg-success small">{{ $rs->total_score }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Tabel Semua Negara --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Semua Negara — Risk Score</h6>
                <input type="text" id="searchCountry" class="form-control form-control-sm w-auto"
                    placeholder="Cari negara...">
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:500px;overflow-y:auto;">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>#</th>
                                <th>Negara</th>
                                <th class="text-center">Weather</th>
                                <th class="text-center">Inflation</th>
                                <th class="text-center">Currency</th>
                                <th class="text-center">News</th>
                                <th class="text-center">Total</th>
                                <th class="text-center">Level</th>
                            </tr>
                        </thead>
                        <tbody id="riskTableBody">
                            @foreach ($riskScores as $i => $rs)
                                <tr class="risk-row"
                                    data-name="{{ strtolower($rs->country?->name ?? '') }}"
                                    data-cca3="{{ $rs->country?->cca3 }}"
                                    id="row-{{ $rs->country?->cca3 }}">
                                    <td class="text-muted small">{{ $i + 1 }}</td>
                                    <td>
                                        @if ($rs->country?->flag_url)
                                            <img src="{{ $rs->country->flag_url }}"
                                                style="height:14px;border-radius:2px;margin-right:4px;">
                                        @endif
                                        {{ $rs->country?->name ?? '—' }}
                                    </td>
                                    <td class="text-center small">{{ $rs->weather_score }}</td>
                                    <td class="text-center small">{{ $rs->inflation_score }}</td>
                                    <td class="text-center small">{{ $rs->currency_score }}</td>
                                    <td class="text-center small">{{ $rs->news_score }}</td>
                                    <td class="text-center"><strong>{{ $rs->total_score }}</strong></td>
                                    <td class="text-center">
                                        @php
                                            $bc = match($rs->risk_level) {
                                                'Low Risk'    => 'bg-success',
                                                'Medium Risk' => 'bg-warning text-dark',
                                                'High Risk'   => 'bg-danger',
                                                default       => 'bg-secondary',
                                            };
                                        @endphp
                                        <span class="badge {{ $bc }} small">{{ $rs->risk_level }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
// Chart distribusi
new Chart(document.getElementById('riskDistChart'), {
    type: 'doughnut',
    data: {
        labels: ['Low Risk', 'Medium Risk', 'High Risk'],
        datasets: [{
            data: [{{ $summary['low'] }}, {{ $summary['medium'] }}, {{ $summary['high'] }}],
            backgroundColor: ['#198754', '#ffc107', '#dc3545'],
            borderWidth: 0,
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'bottom' } }
    }
});

// Search tabel
document.getElementById('searchCountry').addEventListener('input', function() {
    const kw = this.value.toLowerCase();
    document.querySelectorAll('.risk-row').forEach(row => {
        row.style.display = row.dataset.name.includes(kw) ? '' : 'none';
    });
});

// ============================================================
// HIGHLIGHT NEGARA AKTIF (GlobalCountry)
// ============================================================
document.addEventListener('DOMContentLoaded', () => {
    const gc    = GlobalCountry.get();
    const cca3  = gc.cca3;

    if (!cca3) return;

    // Fetch data risk score negara ini dari API
    fetch(`/risk-scoring/detail?cca3=${cca3}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.error) return;

        const risk  = data.risk;
        const card  = document.getElementById('currentCountryRiskCard');
        const inner = document.getElementById('currentRiskCardInner');

        // Tentukan warna border berdasarkan level
        const borderColor = risk.risk_level === 'High Risk' ? '#dc3545'
            : risk.risk_level === 'Medium Risk' ? '#ffc107' : '#198754';
        const badgeClass = risk.risk_level === 'High Risk' ? 'bg-danger'
            : risk.risk_level === 'Medium Risk' ? 'bg-warning text-dark' : 'bg-success';

        // Isi card
        document.getElementById('currentRiskFlag').src     = gc.flag || '';
        document.getElementById('currentRiskCountry').textContent = data.country;
        document.getElementById('currentRiskScore').textContent   = risk.total_score;
        document.getElementById('currentRiskScore').style.color   = borderColor;
        document.getElementById('crWeather').textContent   = risk.scores.weather.score;
        document.getElementById('crInflation').textContent = risk.scores.inflation.score;
        document.getElementById('crCurrency').textContent  = risk.scores.currency.score;
        document.getElementById('crNews').textContent      = risk.scores.news.score;

        const levelEl = document.getElementById('currentRiskLevel');
        levelEl.textContent  = risk.risk_level;
        levelEl.className    = `badge fs-6 px-3 py-2 ${badgeClass}`;
        inner.style.borderLeftColor = borderColor;

        // Tampilkan card
        card.style.display = 'block';

        // Highlight baris di tabel
        const row = document.getElementById(`row-${cca3}`);
        if (row) {
            row.style.background     = borderColor + '22';
            row.style.fontWeight     = '600';
            row.style.borderLeft     = `3px solid ${borderColor}`;
            // Scroll ke baris negara aktif
            setTimeout(() => {
                row.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 500);
        }
    })
    .catch(err => console.error('Risk detail error:', err));
});

// ============================================================
// REFRESH RISK SCORE
// ============================================================
document.getElementById('btnRefreshRisk')?.addEventListener('click', function() {
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Menghitung...';

    fetch('/admin/run-command?cmd=risk%3Acalculate', {
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        } else {
            alert('⚠️ ' + data.message);
        }
    })
    .catch(() => alert('Gagal refresh risk score.'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i>Hitung Ulang Risk Score';
    });
});
</script>
@endpush