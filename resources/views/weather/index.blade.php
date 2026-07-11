@extends('layouts.app')
@section('title', 'Weather Monitoring')
@section('content')
<div class="row g-4">

    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1">Global Weather Monitoring</h4>
                <p class="text-muted mb-0">Peta interaktif kondisi cuaca dan risiko badai per negara</p>
            </div>
            <span class="badge bg-primary fs-6">{{ $summary['total'] }} Negara</span>
        </div>
    </div>

    {{-- Current Country Weather Panel --}}
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="mb-1">Global Weather Monitoring</h4>
                <p class="text-muted mb-0">Peta interaktif kondisi cuaca dan risiko badai per negara</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <small class="text-muted" id="lastUpdatedWeather"></small>
                <button class="btn btn-outline-primary btn-sm" id="btnRefreshWeather">
                    <i class="bi bi-arrow-clockwise me-1"></i>Refresh Cuaca
                </button>
                <span class="badge bg-primary fs-6">{{ $summary['total'] }} Negara</span>
            </div>
        </div>
    </div>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm" style="border-left: 4px solid #dc3545 !important;">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted small mb-1">High Risk</p>
                    <h3 class="mb-0 text-danger">{{ $summary['high'] }}</h3>
                    <small class="text-muted">Badai / Angin Kencang</small>
                </div>
                <i class="bi bi-cloud-lightning-fill text-danger" style="font-size: 2.5rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm" style="border-left: 4px solid #ffc107 !important;">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted small mb-1">Medium Risk</p>
                    <h3 class="mb-0 text-warning">{{ $summary['medium'] }}</h3>
                    <small class="text-muted">Hujan / Angin Sedang</small>
                </div>
                <i class="bi bi-cloud-rain-fill text-warning" style="font-size: 2.5rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm" style="border-left: 4px solid #198754 !important;">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted small mb-1">Low Risk</p>
                    <h3 class="mb-0 text-success">{{ $summary['low'] }}</h3>
                    <small class="text-muted">Kondisi Normal</small>
                </div>
                <i class="bi bi-sun-fill text-success" style="font-size: 2.5rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>

    {{-- Filter --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-2">
                <div class="d-flex gap-2 align-items-center flex-wrap">
                    <span class="text-muted small me-2">Filter Peta:</span>
                    <button class="btn btn-sm btn-danger filter-btn" data-risk="all">Semua ({{ $summary['total'] }})</button>
                    <button class="btn btn-sm btn-outline-danger filter-btn" data-risk="high">High ({{ $summary['high'] }})</button>
                    <button class="btn btn-sm btn-outline-warning filter-btn" data-risk="medium">Medium ({{ $summary['medium'] }})</button>
                    <button class="btn btn-sm btn-outline-success filter-btn" data-risk="low">Low ({{ $summary['low'] }})</button>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div id="weatherMap" style="height: 500px; border-radius: 0 0 12px 12px;"></div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const weatherData = @json($mapData);
const riskColors = { high: '#dc3545', medium: '#ffc107', low: '#198754' };

const map = L.map('weatherMap', { center: [20, 0], zoom: 2 });
setTimeout(() => map.invalidateSize(), 200);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors', maxZoom: 18,
}).addTo(map);

let allMarkers = [];

weatherData.forEach(w => {
    if (!w.lat || !w.lng) return;
    const color = riskColors[w.storm_risk] || '#6c757d';
    const marker = L.circleMarker([w.lat, w.lng], {
        radius: 7, fillColor: color, color: '#fff',
        weight: 1.5, opacity: 1, fillOpacity: 0.85,
    });
    marker.bindPopup(`
        <div style="min-width:180px;font-family:'Inter',sans-serif;">
            <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px;">
                ${w.flag_url ? `<img src="${w.flag_url}" style="height:20px;border-radius:2px;">` : ''}
                <strong>${w.name}</strong>
            </div>
            <table style="width:100%;font-size:0.85rem;">
                <tr><td style="color:#6c757d;">Suhu</td><td><strong>${w.temperature ?? 'N/A'}°C</strong></td></tr>
                <tr><td style="color:#6c757d;">Hujan</td><td><strong>${w.rainfall ?? 'N/A'} mm</strong></td></tr>
                <tr><td style="color:#6c757d;">Angin</td><td><strong>${w.wind_speed ?? 'N/A'} km/h</strong></td></tr>
                <tr><td style="color:#6c757d;">Storm Risk</td><td>
                    <span style="background:${color};color:${w.storm_risk==='medium'?'#000':'#fff'};padding:2px 8px;border-radius:4px;font-size:0.8rem;font-weight:600;">
                        ${w.storm_risk.toUpperCase()}
                    </span>
                </td></tr>
            </table>
        </div>
    `);
    marker.addTo(map);
    allMarkers.push({ marker, risk: w.storm_risk, cca3: w.cca3 });
});

document.querySelectorAll('.filter-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        document.querySelectorAll('.filter-btn').forEach(b => {
            b.className = b.dataset.risk === 'all'
                ? 'btn btn-sm btn-outline-danger filter-btn'
                : `btn btn-sm btn-outline-${b.dataset.risk === 'high' ? 'danger' : b.dataset.risk === 'medium' ? 'warning' : 'success'} filter-btn`;
        });
        this.className = this.className.replace('btn-outline-', 'btn-');
        const f = this.dataset.risk;
        allMarkers.forEach(({ marker, risk }) => {
            f === 'all' || risk === f ? marker.addTo(map) : map.removeLayer(marker);
        });
    });
});

// Auto-load cuaca negara saat ini
document.addEventListener('DOMContentLoaded', () => {
    const gc = GlobalCountry.get();
    loadCurrentCountryWeather(gc.cca3, gc.name);
});

function loadCurrentCountryWeather(cca3, name) {
    document.getElementById('currentCountryLabel').textContent = name;
    fetch(`/weather/country?cca3=${cca3}`, { headers: { 'Accept': 'application/json' } })
    .then(res => res.json())
    .then(data => {
        if (data.error) {
            document.getElementById('currentWeatherData').innerHTML =
                `<div class="col-12 text-muted small">${data.error}</div>`;
            return;
        }
        const stormClass = data.storm_risk === 'high' ? 'danger' : data.storm_risk === 'medium' ? 'warning' : 'success';
        document.getElementById('currentWeatherData').innerHTML = `
            <div class="col-md-3 text-center">
                <i class="bi bi-thermometer-half text-danger" style="font-size:1.5rem;"></i>
                <p class="small text-muted mb-0 mt-1">Suhu</p>
                <strong>${data.temperature}°C</strong>
            </div>
            <div class="col-md-3 text-center">
                <i class="bi bi-cloud-rain text-primary" style="font-size:1.5rem;"></i>
                <p class="small text-muted mb-0 mt-1">Curah Hujan</p>
                <strong>${data.rainfall} mm</strong>
            </div>
            <div class="col-md-3 text-center">
                <i class="bi bi-wind text-info" style="font-size:1.5rem;"></i>
                <p class="small text-muted mb-0 mt-1">Kecepatan Angin</p>
                <strong>${data.wind_speed} km/h</strong>
            </div>
            <div class="col-md-3 text-center">
                <i class="bi bi-exclamation-triangle text-${stormClass}" style="font-size:1.5rem;"></i>
                <p class="small text-muted mb-0 mt-1">Storm Risk</p>
                <span class="badge bg-${stormClass}">${data.storm_risk.toUpperCase()}</span>
            </div>
        `;
        // Zoom ke negara di peta
        if (data.lat && data.lng) {
            map.setView([data.lat, data.lng], 5);
            const found = allMarkers.find(m => m.cca3 === cca3);
            if (found) found.marker.openPopup();
        }
    })
    .catch(() => {
        document.getElementById('currentWeatherData').innerHTML =
            '<div class="col-12 text-muted small">Gagal mengambil data cuaca.</div>';
    });
}

// Last updated info
const lwu = localStorage.getItem('last_weather_update');
if (lwu) {
    const diff = Math.round((Date.now() - parseInt(lwu)) / 60000);
    document.getElementById('lastUpdatedWeather').textContent =
        `Update: ${diff < 1 ? 'baru saja' : diff + ' mnt lalu'}`;
}

document.getElementById('btnRefreshWeather')?.addEventListener('click', function() {
    if (!confirm('Refresh data cuaca semua negara? Ini akan memakan waktu ~30 detik.')) return;
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Updating...';
    fetch('/refresh/weather', {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        }
    })
    .then(r => r.json())
    .then(d => {
        localStorage.setItem('last_weather_update', Date.now());
        alert('✅ ' + d.message);
        window.location.reload();
    })
    .catch(() => alert('Gagal refresh.'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i>Refresh Cuaca';
    });
});
</script>
@endpush