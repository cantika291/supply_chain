@extends('layouts.app')

@section('title', 'Country Dashboard')

@section('content')
<div class="row g-4">

    {{-- Header & Dropdown Pilih Negara --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="row align-items-center g-3">
                    <div class="col-md-6">
                        <h4 class="mb-1">Country Dashboard</h4>
                        <p class="text-muted mb-0">Pilih negara untuk melihat data ekonomi, cuaca, dan risiko rantai pasok.</p>
                    </div>
                    <div class="col-md-6">
                        <select id="countrySelect" class="form-select form-select-lg">
                            <option value="">-- Pilih Negara --</option>
                            @foreach ($countries as $country)
                                <option value="{{ $country->cca3 }}"
                                    {{ $country->cca3 === 'IDN' ? 'selected' : '' }}>
                                    {{ $country->name }} ({{ $country->cca3 }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Loading Spinner --}}
    <div class="col-12 text-center py-3 d-none" id="loadingSpinner">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
        <p class="text-muted mt-2">Mengambil data negara...</p>
    </div>

    {{-- Country Info Card --}}
    <div class="col-12 d-none" id="countryInfoSection">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center gap-3">
                    <img id="countryFlag" src="" alt="Flag" style="height: 50px; border-radius: 4px; box-shadow: 0 2px 8px rgba(0,0,0,0.15);">
                    <div>
                        <h4 class="mb-0" id="countryName">—</h4>
                        <p class="text-muted mb-0" id="countryDetail">—</p>
                    </div>
                    <div class="ms-auto">
                        <span id="riskBadge" class="badge fs-6 px-3 py-2">—</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Stat Cards: GDP, Inflasi, Populasi, Kurs --}}
    <div class="col-12 d-none" id="statsSection">
        <div class="row g-3">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-graph-up-arrow text-primary" style="font-size: 1.75rem;"></i>
                        <h6 class="text-muted mt-2 mb-1">GDP</h6>
                        <h5 class="mb-0" id="gdpValue">—</h5>
                        <small class="text-muted" id="gdpYear"></small>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-percent text-warning" style="font-size: 1.75rem;"></i>
                        <h6 class="text-muted mt-2 mb-1">Inflasi</h6>
                        <h5 class="mb-0" id="inflationValue">—</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-people-fill text-info" style="font-size: 1.75rem;"></i>
                        <h6 class="text-muted mt-2 mb-1">Populasi</h6>
                        <h5 class="mb-0" id="populationValue">—</h5>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body text-center">
                        <i class="bi bi-currency-exchange text-success" style="font-size: 1.75rem;"></i>
                        <h6 class="text-muted mt-2 mb-1">Kurs</h6>
                        <h5 class="mb-0" id="rateValue">—</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Cuaca + Risk Score --}}
    <div class="col-md-6 d-none" id="weatherSection">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="mb-0"><i class="bi bi-cloud-sun me-2"></i>Kondisi Cuaca</h6>
            </div>
            <div class="card-body">
                <div class="row text-center g-3">
                    <div class="col-4">
                        <i class="bi bi-thermometer-half text-danger" style="font-size: 1.5rem;"></i>
                        <p class="small text-muted mb-0 mt-1">Suhu</p>
                        <strong id="weatherTemp">—</strong>
                    </div>
                    <div class="col-4">
                        <i class="bi bi-cloud-rain text-primary" style="font-size: 1.5rem;"></i>
                        <p class="small text-muted mb-0 mt-1">Curah Hujan</p>
                        <strong id="weatherRain">—</strong>
                    </div>
                    <div class="col-4">
                        <i class="bi bi-wind text-info" style="font-size: 1.5rem;"></i>
                        <p class="small text-muted mb-0 mt-1">Angin</p>
                        <strong id="weatherWind">—</strong>
                    </div>
                </div>
                <hr>
                <div class="text-center">
                    <p class="text-muted small mb-1">Storm Risk Level</p>
                    <span id="stormRiskBadge" class="badge fs-6 px-3 py-2">—</span>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6 d-none" id="riskSection">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="mb-0"><i class="bi bi-shield-exclamation me-2"></i>Risk Score Breakdown</h6>
            </div>
            <div class="card-body">
                <canvas id="riskRadarChart" style="max-height: 220px;"></canvas>
            </div>
        </div>
    </div>


</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
<script>
let gdpChart = null;
let riskChart = null;

const countrySelect = document.getElementById('countrySelect');

countrySelect.addEventListener('change', function () {

    const cca3 = this.value;

    if (!cca3) return;

    fetchCountryData(cca3);

    // Simpan ke Laravel Session
    fetch('/current-country', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute('content')
        },
        body: JSON.stringify({
            country: cca3
        })
    })
    .then(response => response.json())
    .then(() => {

        // baru load data dashboard
        fetchCountryData(cca3);

    })
    .catch(error => {

        console.error(error);

    });

});

// Auto-load negara terakhir yang dipilih user (atau Indonesia kalau pertama kali)
document.addEventListener('DOMContentLoaded', () => {
    const gc = GlobalCountry.get();
    const select = document.getElementById('countrySelect');
    const optionExists = Array.from(select.options).some(opt => opt.value === gc.cca3);
    const toLoad = optionExists ? gc.cca3 : 'IDN';
    select.value = toLoad;
    fetchCountryData(toLoad);
});



function fetchCountryData(cca3) {
    showLoading(true);

    fetch(`/countries/data?cca3=${cca3}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.error) {
            alert(data.error);
            return;
        }
        renderCountryData(data);
    })
    .catch(err => {
        console.error('Error fetching country data:', err);
        alert('Gagal mengambil data negara. Coba lagi.');
    })
    .finally(() => showLoading(false));
}

function renderCountryData(data) {
    const { country, economic, weather, risk, exchange_rate, economic_history } = data;

    // Simpan ke GlobalCountry context supaya semua halaman lain bisa baca
   GlobalCountry.set(
    country.cca3,
    country.name,
    country.flag_url || '',
    country.currency_code || ''
);

    // Country Info
    show('countryInfoSection');
    document.getElementById('countryFlag').src = country.flag_url || '';
    document.getElementById('countryName').textContent = country.name;
    document.getElementById('countryDetail').textContent =
        `${country.capital || '—'} · ${country.region || '—'} · ${country.currency_code || '—'}`;

    // Risk Badge
    const riskBadge = document.getElementById('riskBadge');
    if (risk) {
        riskBadge.textContent = `${risk.risk_level} (${risk.total_score})`;
        riskBadge.className = 'badge fs-6 px-3 py-2 ' + riskLevelClass(risk.risk_level);
    } else {
        riskBadge.textContent = 'No Risk Data';
        riskBadge.className = 'badge fs-6 px-3 py-2 bg-secondary';
    }

    // Stats
    show('statsSection');
    document.getElementById('gdpValue').textContent = economic?.gdp_formatted || 'N/A';
    document.getElementById('gdpYear').textContent = economic?.year ? `Data ${economic.year}` : '';
    document.getElementById('inflationValue').textContent = economic?.inflation
        ? parseFloat(economic.inflation).toFixed(2) + '%'
        : 'N/A';
    document.getElementById('populationValue').textContent = economic?.population || 'N/A';
    document.getElementById('rateValue').textContent = exchange_rate?.formatted || 'N/A';

    // Weather
    if (weather) {
        show('weatherSection');
        document.getElementById('weatherTemp').textContent = `${weather.temperature}°C`;
        document.getElementById('weatherRain').textContent = `${weather.rainfall} mm`;
        document.getElementById('weatherWind').textContent = `${weather.wind_speed} km/h`;
        const stormBadge = document.getElementById('stormRiskBadge');
        stormBadge.textContent = weather.storm_risk.toUpperCase();
        stormBadge.className = 'badge fs-6 px-3 py-2 ' + stormClass(weather.storm_risk);
    }

    // Risk Radar Chart
    if (risk) {
        show('riskSection');
        renderRiskChart(risk);
    }

    // GDP Trend Chart
    if (economic_history && economic_history.length > 0) {
        show('chartSection');
        renderGdpChart(economic_history);
    }
}

function renderGdpChart(history) {
    const labels = history.map(h => h.year);
    const gdpData = history.map(h => h.gdp ? (h.gdp / 1e12).toFixed(2) : 0);
    const inflationData = history.map(h => h.inflation_rate ? parseFloat(h.inflation_rate).toFixed(2) : 0);

    if (gdpChart) gdpChart.destroy();

    gdpChart = new Chart(document.getElementById('gdpChart'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'GDP (Trillion USD)',
                    data: gdpData,
                    backgroundColor: 'rgba(13, 110, 253, 0.7)',
                    borderRadius: 6,
                    yAxisID: 'y',
                },
                {
                    label: 'Inflasi (%)',
                    data: inflationData,
                    type: 'line',
                    borderColor: '#ffc107',
                    backgroundColor: 'rgba(255,193,7,0.1)',
                    borderWidth: 2,
                    pointRadius: 4,
                    fill: false,
                    yAxisID: 'y1',
                }
            ]
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'top' } },
            scales: {
                y:  { type: 'linear', position: 'left',  title: { display: true, text: 'GDP (Trillion USD)' } },
                y1: { type: 'linear', position: 'right', title: { display: true, text: 'Inflasi (%)' }, grid: { drawOnChartArea: false } }
            }
        }
    });
}

function renderRiskChart(risk) {
    if (riskChart) riskChart.destroy();

    riskChart = new Chart(document.getElementById('riskRadarChart'), {
        type: 'radar',
        data: {
            labels: ['Weather', 'Inflation', 'Currency', 'News Sentiment'],
            datasets: [{
                label: 'Risk Score',
                data: [risk.weather_score, risk.inflation_score, risk.currency_score, risk.news_score],
                backgroundColor: 'rgba(220, 53, 69, 0.2)',
                borderColor: '#dc3545',
                borderWidth: 2,
                pointBackgroundColor: '#dc3545',
            }]
        },
        options: {
            responsive: true,
            scales: {
                r: {
                    min: 0,
                    max: 100,
                    ticks: { stepSize: 25 }
                }
            },
            plugins: { legend: { display: false } }
        }
    });
}

function riskLevelClass(level) {
    if (level === 'Low Risk') return 'bg-success';
    if (level === 'Medium Risk') return 'bg-warning text-dark';
    return 'bg-danger';
}

function stormClass(risk) {
    if (risk === 'high') return 'bg-danger';
    if (risk === 'medium') return 'bg-warning text-dark';
    return 'bg-success';
}

function show(id) {
    document.getElementById(id)?.classList.remove('d-none');
}

function showLoading(state) {
    const spinner = document.getElementById('loadingSpinner');
    if (state) {
        spinner.classList.remove('d-none');
    } else {
        spinner.classList.add('d-none');
    }
}
</script>
@endpush