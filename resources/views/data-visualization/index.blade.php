@extends('layouts.app')
@section('title', 'Data Visualization')
@section('content')
<div class="row g-4">

    {{-- Header --}}
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="mb-1">Data Visualization Dashboard</h4>
                <p class="text-muted mb-0">Grafik tren GDP, Inflasi, Kurs, dan Risk Score per negara</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <select id="vizCountrySelect" class="form-select form-select-sm" style="min-width: 220px;">
                    @foreach ($countries as $c)
                        <option value="{{ $c->cca3 }}">{{ $c->name }} ({{ $c->cca3 }})</option>
                    @endforeach
                </select>
                <button id="btnLoadViz" class="btn btn-primary btn-sm">
                    <i class="bi bi-graph-up me-1"></i>Tampilkan
                </button>
            </div>
        </div>
    </div>

    {{-- Country Header --}}
    <div class="col-12 d-none" id="vizCountryHeader">
        <div class="card border-0 shadow-sm">
            <div class="card-body d-flex align-items-center gap-3 py-2">
                <img id="vizFlag" src="" style="height: 36px; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                <div>
                    <h6 class="mb-0" id="vizCountryName">—</h6>
                    <small class="text-muted" id="vizCountryCurrency">—</small>
                </div>
                <div class="ms-auto">
                    <span class="badge bg-primary" id="vizDataPoints">—</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Loading --}}
    <div class="col-12 text-center d-none" id="vizLoading">
        <div class="spinner-border text-primary"></div>
        <p class="text-muted mt-2">Mengambil data tren...</p>
    </div>

    {{-- Chart 1: GDP Trend --}}
    <div class="col-md-6 d-none viz-chart" id="gdpSection">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="mb-0">
                    <i class="bi bi-graph-up-arrow text-primary me-2"></i>GDP Trend
                </h6>
            </div>
            <div class="card-body">
                <canvas id="gdpTrendChart" style="max-height: 250px;"></canvas>
                <p class="text-muted small text-center mt-2 mb-0" id="gdpTrendInfo">—</p>
            </div>
        </div>
    </div>

    {{-- Chart 2: Inflation Trend --}}
    <div class="col-md-6 d-none viz-chart" id="inflationSection">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="mb-0">
                    <i class="bi bi-percent text-warning me-2"></i>Inflation Trend
                </h6>
            </div>
            <div class="card-body">
                <canvas id="inflationTrendChart" style="max-height: 250px;"></canvas>
                <p class="text-muted small text-center mt-2 mb-0" id="inflationTrendInfo">—</p>
            </div>
        </div>
    </div>

    {{-- Chart 3: Currency Trend --}}
    <div class="col-md-6 d-none viz-chart" id="currencySection">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="mb-0">
                    <i class="bi bi-currency-exchange text-success me-2"></i>Currency Trend (vs USD)
                </h6>
            </div>
            <div class="card-body">
                <canvas id="currencyTrendChart" style="max-height: 250px;"></canvas>
                <p class="text-muted small text-center mt-2 mb-0" id="currencyTrendInfo">—</p>
            </div>
        </div>
    </div>

    {{-- Chart 4: Risk Score Trend --}}
    <div class="col-md-6 d-none viz-chart" id="riskSection">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="mb-0">
                    <i class="bi bi-shield-exclamation text-danger me-2"></i>Risk Score Trend
                </h6>
            </div>
            <div class="card-body">
                <canvas id="riskTrendChart" style="max-height: 250px;"></canvas>
                <p class="text-muted small text-center mt-2 mb-0" id="riskTrendInfo">—</p>
            </div>
        </div>
    </div>

    

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
let charts = {};

document.addEventListener('DOMContentLoaded', () => {
    const gc = GlobalCountry.get();
    const select = document.getElementById('vizCountrySelect');
    const exists = Array.from(select.options).some(o => o.value === gc.cca3);
    if (exists) select.value = gc.cca3;
    loadVizData(select.value);
});

document.getElementById('btnLoadViz').addEventListener('click', () => {
    loadVizData(document.getElementById('vizCountrySelect').value);
});

document.getElementById('vizCountrySelect').addEventListener('change', function() {
    loadVizData(this.value);
});

function loadVizData(cca3) {
    if (!cca3) return;
    document.getElementById('vizLoading').classList.remove('d-none');
    document.getElementById('vizCountryHeader').classList.add('d-none');
    document.querySelectorAll('.viz-chart').forEach(el => el.classList.add('d-none'));

    fetch(`/data-visualization/data?cca3=${cca3}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.error) {
            alert(data.error);
            return;
        }
        renderViz(data);
    })
    .catch(err => {
        console.error(err);
        alert('Gagal mengambil data visualisasi.');
    })
    .finally(() => {
        document.getElementById('vizLoading').classList.add('d-none');
    });
}

function renderViz(data) {
    // Country header
    document.getElementById('vizCountryHeader').classList.remove('d-none');
    document.getElementById('vizFlag').src = data.country.flag_url || '';
    document.getElementById('vizCountryName').textContent = data.country.name;
    document.getElementById('vizCountryCurrency').textContent =
        `Mata uang: ${data.country.currency_code || 'N/A'}`;
    document.getElementById('vizDataPoints').textContent =
        `${data.economic_history.length} tahun data ekonomi`;

    // Destroy existing charts
    Object.values(charts).forEach(c => { try { c.destroy(); } catch(e) {} });
    charts = {};

    // Show all sections
    document.querySelectorAll('.viz-chart').forEach(el => el.classList.remove('d-none'));

    // === GDP Trend ===
    if (data.economic_history.length > 0) {
        const labels  = data.economic_history.map(h => h.year);
        const gdpData = data.economic_history.map(h =>
            h.gdp ? parseFloat((h.gdp / 1e12).toFixed(3)) : null
        );
        if (charts.gdp) charts.gdp.destroy();
        charts.gdp = new Chart(document.getElementById('gdpTrendChart'), {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'GDP (Trillion USD)',
                    data: gdpData,
                    borderColor: '#0d6efd',
                    backgroundColor: 'rgba(13,110,253,0.08)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 4,
                    spanGaps: true,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: false,
                    ticks: { callback: v => v + 'T' } } }
            }
        });
        const first = labels[0], last = labels[labels.length - 1];
        document.getElementById('gdpTrendInfo').textContent =
            `Data tahun ${first} – ${last} (${labels.length} tahun)`;
    } else {
        document.getElementById('gdpTrendInfo').textContent = 'Data GDP tidak tersedia.';
    }

    // === Inflation Trend ===
    if (data.economic_history.length > 0) {
        const labels   = data.economic_history.map(h => h.year);
        const inflData = data.economic_history.map(h =>
            h.inflation_rate ? parseFloat(parseFloat(h.inflation_rate).toFixed(2)) : null
        );
        if (charts.infl) charts.infl.destroy();
        charts.infl = new Chart(document.getElementById('inflationTrendChart'), {
            type: 'bar',
            data: {
                labels,
                datasets: [{
                    label: 'Inflasi (%)',
                    data: inflData,
                    backgroundColor: inflData.map(v =>
                        v === null ? '#ccc' :
                        v > 5 ? 'rgba(220,53,69,0.75)' :
                        v > 2 ? 'rgba(255,193,7,0.75)' :
                        'rgba(25,135,84,0.75)'
                    ),
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true,
                    ticks: { callback: v => v + '%' } } }
            }
        });
        document.getElementById('inflationTrendInfo').textContent =
            '🟢 <2% Normal | 🟡 2-5% Waspada | 🔴 >5% Tinggi';
    }

    // === Currency Trend ===
    if (data.currency_history.length > 0) {
        const labels   = data.currency_history.map(h => h.date);
        const rateData = data.currency_history.map(h => h.rate);
        if (charts.currency) charts.currency.destroy();
        charts.currency = new Chart(document.getElementById('currencyTrendChart'), {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: `${data.country.currency_code} per 1 USD`,
                    data: rateData,
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25,135,84,0.08)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointRadius: labels.length > 5 ? 3 : 5,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: {
                    y: { ticks: { callback: v => v.toLocaleString() } }
                }
            }
        });
        document.getElementById('currencyTrendInfo').textContent =
            `${labels.length} hari data kurs historis — 1 USD = ... ${data.country.currency_code}`;
    } else {
        document.getElementById('currencyTrendInfo').textContent =
            'Belum ada histori kurs. Data akan bertambah setiap hari sync dijalankan.';
    }

    // === Risk Score Trend ===
    if (data.risk_history.length > 0) {
        const labels    = data.risk_history.map(h => h.date);
        const scoreData = data.risk_history.map(h => h.score);
        const ptColors  = data.risk_history.map(h =>
            h.risk_level === 'High Risk' ? '#dc3545' :
            h.risk_level === 'Medium Risk' ? '#ffc107' : '#198754'
        );
        if (charts.risk) charts.risk.destroy();
        charts.risk = new Chart(document.getElementById('riskTrendChart'), {
            type: 'line',
            data: {
                labels,
                datasets: [{
                    label: 'Risk Score',
                    data: scoreData,
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220,53,69,0.08)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3,
                    pointRadius: 5,
                    pointBackgroundColor: ptColors,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            afterLabel: ctx => {
                                const h = data.risk_history[ctx.dataIndex];
                                return `Level: ${h.risk_level}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        min: 0, max: 100,
                        ticks: {
                            callback: v =>
                                v >= 67 ? `${v} ⚠️` :
                                v >= 34 ? `${v} ⚡` : `${v} ✅`
                        }
                    }
                }
            }
        });
        document.getElementById('riskTrendInfo').textContent =
            `${labels.length} kalkulasi historis — 🔴 High ≥67 | 🟡 Medium ≥34 | 🟢 Low <34`;
    } else {
        document.getElementById('riskTrendInfo').textContent =
            'Belum ada histori risk score. Jalankan risk:calculate beberapa kali.';
    }
}
</script>
@endpush