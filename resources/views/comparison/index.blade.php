@extends('layouts.app')

@section('title', 'Country Comparison')

@section('content')
<div class="row g-4">

    {{-- Header --}}
    <div class="col-12">
        <h4 class="mb-1">Country Comparison Engine</h4>
        <p class="text-muted mb-0">Bandingkan 2 negara berdasarkan GDP, Inflasi, Risiko, Cuaca, dan Kurs</p>
    </div>

    {{-- Selector --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Negara A</label>
                        <select id="countryA" class="form-select">
                            @foreach ($countries as $c)
                                <option value="{{ $c->cca3 }}" {{ $c->cca3 === 'DEU' ? 'selected' : '' }}>
                                    {{ $c->name }} ({{ $c->cca3 }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 text-center">
                        <span class="badge bg-secondary fs-5 px-3 py-2">VS</span>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label fw-semibold">Negara B</label>
                        <select id="countryB" class="form-select">
                            @foreach ($countries as $c)
                                <option value="{{ $c->cca3 }}" {{ $c->cca3 === 'AUS' ? 'selected' : '' }}>
                                    {{ $c->name }} ({{ $c->cca3 }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 text-center mt-2">
                        <button id="btnCompare" class="btn btn-primary px-5">
                            <i class="bi bi-bar-chart-steps me-2"></i>Bandingkan Sekarang
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Loading --}}
    <div class="col-12 text-center d-none" id="loadingSpinner">
        <div class="spinner-border text-primary"></div>
        <p class="text-muted mt-2">Mengambil data perbandingan...</p>
    </div>

    {{-- Hasil Perbandingan --}}
    <div class="col-12 d-none" id="comparisonResult">

        {{-- Header Negara --}}
        <div class="row g-3 mb-4">
            <div class="col-md-5">
                <div class="card border-0 shadow-sm text-center p-3">
                    <img id="flagA" src="" style="height: 50px; margin: 0 auto 12px; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                    <h5 id="nameA" class="mb-1">—</h5>
                    <small id="detailA" class="text-muted">—</small>
                </div>
            </div>
            <div class="col-md-2 d-flex align-items-center justify-content-center">
                <span class="badge bg-dark fs-4 px-4 py-3">VS</span>
            </div>
            <div class="col-md-5">
                <div class="card border-0 shadow-sm text-center p-3">
                    <img id="flagB" src="" style="height: 50px; margin: 0 auto 12px; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                    <h5 id="nameB" class="mb-1">—</h5>
                    <small id="detailB" class="text-muted">—</small>
                </div>
            </div>
        </div>

        {{-- Tabel Perbandingan --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="mb-0">Perbandingan Detail</h6>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Indikator</th>
                            <th class="text-center" id="headerA">Negara A</th>
                            <th class="text-center" id="headerB">Negara B</th>
                            <th class="text-center">Lebih Baik</th>
                        </tr>
                    </thead>
                    <tbody id="comparisonTable">
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Chart Perbandingan --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="mb-0">Visualisasi Perbandingan</h6>
            </div>
            <div class="card-body">
                <canvas id="comparisonChart" style="max-height: 350px;"></canvas>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
let compChart = null;

// Auto-load saat halaman dibuka
document.addEventListener('DOMContentLoaded', () => {
    const gc = GlobalCountry.get();
    const selectA = document.getElementById('countryA');
    const existsA = Array.from(selectA.options).some(o => o.value === gc.cca3);
    if (existsA) selectA.value = gc.cca3;
    doCompare();
});

document.getElementById('btnCompare').addEventListener('click', doCompare);

function doCompare() {
    const cca3A = document.getElementById('countryA').value;
    const cca3B = document.getElementById('countryB').value;

    if (cca3A === cca3B) {
        alert('Pilih 2 negara yang berbeda untuk dibandingkan.');
        return;
    }

    document.getElementById('loadingSpinner').classList.remove('d-none');
    document.getElementById('comparisonResult').classList.add('d-none');

    fetch(`/comparison/compare?country_a=${cca3A}&country_b=${cca3B}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        renderComparison(data.country_a, data.country_b);
    })
    .catch(err => {
        console.error(err);
        alert('Gagal mengambil data perbandingan.');
    })
    .finally(() => {
        document.getElementById('loadingSpinner').classList.add('d-none');
        document.getElementById('comparisonResult').classList.remove('d-none');
    });
}

function renderComparison(a, b) {
    // Header negara
    document.getElementById('flagA').src = a.flag_url || '';
    document.getElementById('flagB').src = b.flag_url || '';
    document.getElementById('nameA').textContent = a.name;
    document.getElementById('nameB').textContent = b.name;
    document.getElementById('detailA').textContent = `${a.capital} · ${a.region} · ${a.currency_code}`;
    document.getElementById('detailB').textContent = `${b.capital} · ${b.region} · ${b.currency_code}`;
    document.getElementById('headerA').textContent = a.name;
    document.getElementById('headerB').textContent = b.name;

    // Data untuk tabel
    const rows = [
        {
            label: '💰 GDP',
            valA: a.economic?.gdp_formatted || 'N/A',
            valB: b.economic?.gdp_formatted || 'N/A',
            rawA: a.economic?.gdp || 0,
            rawB: b.economic?.gdp || 0,
            higher_is_better: true,
        },
        {
            label: '📈 Inflasi',
            valA: a.economic?.inflation ? a.economic.inflation.toFixed(2) + '%' : 'N/A',
            valB: b.economic?.inflation ? b.economic.inflation.toFixed(2) + '%' : 'N/A',
            rawA: a.economic?.inflation || 0,
            rawB: b.economic?.inflation || 0,
            higher_is_better: false,
        },
        {
            label: '👥 Populasi',
            valA: a.economic?.population ? Number(a.economic.population).toLocaleString() : 'N/A',
            valB: b.economic?.population ? Number(b.economic.population).toLocaleString() : 'N/A',
            rawA: a.economic?.population || 0,
            rawB: b.economic?.population || 0,
            higher_is_better: null, // netral
        },
        {
            label: '🛡️ Risk Score',
            valA: a.risk?.total_score ? `${a.risk.total_score} (${a.risk.risk_level})` : 'N/A',
            valB: b.risk?.total_score ? `${b.risk.total_score} (${b.risk.risk_level})` : 'N/A',
            rawA: a.risk?.total_score || 0,
            rawB: b.risk?.total_score || 0,
            higher_is_better: false,
        },
        {
            label: '🌡️ Suhu',
            valA: a.weather?.temperature ? `${a.weather.temperature}°C` : 'N/A',
            valB: b.weather?.temperature ? `${b.weather.temperature}°C` : 'N/A',
            rawA: a.weather?.temperature || 0,
            rawB: b.weather?.temperature || 0,
            higher_is_better: null,
        },
        {
            label: '🌪️ Storm Risk',
            valA: a.weather?.storm_risk?.toUpperCase() || 'N/A',
            valB: b.weather?.storm_risk?.toUpperCase() || 'N/A',
            rawA: stormToNum(a.weather?.storm_risk),
            rawB: stormToNum(b.weather?.storm_risk),
            higher_is_better: false,
        },
        {
            label: '💱 Kurs (per USD)',
            valA: a.exchange_rate ? Number(a.exchange_rate).toLocaleString() + ' ' + a.currency_code : 'N/A',
            valB: b.exchange_rate ? Number(b.exchange_rate).toLocaleString() + ' ' + b.currency_code : 'N/A',
            rawA: 0,
            rawB: 0,
            higher_is_better: null,
        },
    ];

    // Render tabel
    const tbody = document.getElementById('comparisonTable');
    tbody.innerHTML = rows.map(row => {
        let winner = '—';
        if (row.higher_is_better === true) {
            winner = row.rawA > row.rawB
                ? `<span class="badge bg-primary">${a.name}</span>`
                : row.rawB > row.rawA
                    ? `<span class="badge bg-success">${b.name}</span>`
                    : '<span class="badge bg-secondary">Sama</span>';
        } else if (row.higher_is_better === false) {
            winner = row.rawA < row.rawB
                ? `<span class="badge bg-primary">${a.name}</span>`
                : row.rawB < row.rawA
                    ? `<span class="badge bg-success">${b.name}</span>`
                    : '<span class="badge bg-secondary">Sama</span>';
        }

        return `<tr>
            <td class="fw-semibold">${row.label}</td>
            <td class="text-center">${row.valA}</td>
            <td class="text-center">${row.valB}</td>
            <td class="text-center">${winner}</td>
        </tr>`;
    }).join('');

    // Chart radar perbandingan
    const normalize = (val, max) => max > 0 ? Math.round((val / max) * 100) : 0;
    const maxGdp   = Math.max(a.economic?.gdp || 0, b.economic?.gdp || 0);
    const maxPop   = Math.max(a.economic?.population || 0, b.economic?.population || 0);

    const dataA = [
        normalize(a.economic?.gdp || 0, maxGdp),
        Math.min((a.economic?.inflation || 0) * 10, 100),
        100 - (a.risk?.total_score || 50),
        normalize(a.economic?.population || 0, maxPop),
        stormToNum(a.weather?.storm_risk) === 0 ? 100 : stormToNum(a.weather?.storm_risk) === 1 ? 50 : 10,
    ];

    const dataB = [
        normalize(b.economic?.gdp || 0, maxGdp),
        Math.min((b.economic?.inflation || 0) * 10, 100),
        100 - (b.risk?.total_score || 50),
        normalize(b.economic?.population || 0, maxPop),
        stormToNum(b.weather?.storm_risk) === 0 ? 100 : stormToNum(b.weather?.storm_risk) === 1 ? 50 : 10,
    ];

    if (compChart) compChart.destroy();

    compChart = new Chart(document.getElementById('comparisonChart'), {
        type: 'bar',
        data: {
            labels: ['GDP (relatif)', 'Inflasi (%×10)', 'Safety Score', 'Populasi (relatif)', 'Cuaca Score'],
            datasets: [
                {
                    label: a.name,
                    data: dataA,
                    backgroundColor: 'rgba(13, 110, 253, 0.7)',
                    borderRadius: 6,
                },
                {
                    label: b.name,
                    data: dataB,
                    backgroundColor: 'rgba(25, 135, 84, 0.7)',
                    borderRadius: 6,
                }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'top' } },
            scales: {
                y: { beginAtZero: true, max: 100 }
            }
        }
    });
}

function stormToNum(risk) {
    if (risk === 'high')   return 2;
    if (risk === 'medium') return 1;
    return 0;
}
</script>
@endpush