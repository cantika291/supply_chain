@extends('layouts.app')
@section('title', 'Port Dashboard')
@section('content')
<div class="row g-4">

    {{-- Header --}}
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="mb-1">Port Location Dashboard</h4>
                <p class="text-muted mb-0">
                    Data World Port Index (NGIA) — {{ number_format($totalPorts) }} pelabuhan di {{ $totalCountries }} negara
                </p>
            </div>
            <span class="badge bg-primary fs-6">{{ number_format($totalPorts) }} Pelabuhan</span>
        </div>
    </div>

    {{-- Stats Row --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm" style="border-left:4px solid #0d6efd !important;">
            <div class="card-body d-flex justify-content-between align-items-center py-3">
                <div>
                    <p class="text-muted small mb-1">Total Pelabuhan</p>
                    <h3 class="mb-0 text-primary fw-bold">{{ number_format($totalPorts) }}</h3>
                    <small class="text-muted">di seluruh dunia</small>
                </div>
                <i class="bi bi-geo-alt-fill text-primary" style="font-size:2rem;opacity:0.2;"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm" style="border-left:4px solid #198754 !important;">
            <div class="card-body d-flex justify-content-between align-items-center py-3">
                <div>
                    <p class="text-muted small mb-1">Negara</p>
                    <h3 class="mb-0 text-success fw-bold">{{ $totalCountries }}</h3>
                    <small class="text-muted">memiliki pelabuhan</small>
                </div>
                <i class="bi bi-globe text-success" style="font-size:2rem;opacity:0.2;"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body py-2 px-3">
                <p class="text-muted small mb-2 fw-semibold">Top Negara (Pelabuhan Terbanyak)</p>
                @foreach ($topCountries->take(5) as $country)
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <div class="d-flex align-items-center gap-1" style="min-width:0;">
                            @if ($country->flag_url)
                                <img src="{{ $country->flag_url }}" style="height:14px;border-radius:2px;flex-shrink:0;">
                            @endif
                            <span class="small text-truncate">{{ $country->name }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-1 ms-2" style="flex-shrink:0;">
                            <div class="progress" style="width:60px;height:5px;">
                                <div class="progress-bar bg-primary"
                                    style="width:{{ min(($country->ports_count / $topCountries->first()->ports_count)*100,100) }}%">
                                </div>
                            </div>
                            <span class="badge bg-light text-dark border" style="font-size:0.7rem;">
                                {{ $country->ports_count }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Search & Filter --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body py-2">
                <div class="row g-2 align-items-center">
                    <div class="col-md-4">
                        <label class="form-label small text-muted mb-1">
                            <i class="bi bi-search me-1"></i>Cari Nama Pelabuhan
                        </label>
                        <input type="text" id="portSearch" class="form-control form-control-sm"
                            placeholder="Contoh: Rotterdam, Tanjung Priok...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small text-muted mb-1">
                            <i class="bi bi-globe me-1"></i>Cari Negara
                        </label>
                        <select id="countryFilter" class="form-select form-select-sm">
                            <option value="">-- Semua Negara --</option>
                            @foreach ($countries->sortBy('name') as $country)
                                <option value="{{ $country->cca3 }}">
                                    {{ $country->name }} ({{ $country->ports_count }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-auto d-flex align-items-end gap-2">
                        <button id="btnSearch" class="btn btn-primary btn-sm">
                            <i class="bi bi-search me-1"></i>Cari
                        </button>
                        <button id="btnReset" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>Reset
                        </button>
                    </div>
                    <div class="col-md-auto ms-auto d-flex align-items-end">
                        <small class="text-muted" id="searchResultInfo">
                            <i class="bi bi-info-circle me-1"></i>
                            500 sampel acak ditampilkan di peta
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Peta Leaflet --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3 pb-2 d-flex justify-content-between align-items-center">
                <h6 class="mb-0">
                    <i class="bi bi-map me-2 text-primary"></i>Peta Pelabuhan Interaktif
                    <small class="text-muted fw-normal ms-2">— Klik marker untuk detail pelabuhan</small>
                </h6>
                <span class="badge bg-info text-dark" id="mapMarkerCount">—</span>
            </div>
            <div class="card-body p-0">
                <div id="portMap" style="height: 520px; border-radius: 0 0 12px 12px;"></div>
            </div>
        </div>
    </div>

    {{-- Tipe Pelabuhan --}}
    @if ($harborTypes->isNotEmpty())
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 pt-3">
                    <h6 class="mb-0">Tipe Pelabuhan</h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        @foreach ($harborTypes as $type)
                            <div class="col-md-auto">
                                <span class="badge bg-light text-dark border fs-6 px-3 py-2">
                                    {{ $type->harbor_type }}: {{ number_format($type->total) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>
@endsection

@push('scripts')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const portData = @json($mapData);

// Inisialisasi peta
const map = L.map('portMap', {
    center: [20, 0],
    zoom: 2,
    minZoom: 2,
    maxZoom: 16,
});

window.addEventListener('load', () => {
    setTimeout(() => map.invalidateSize(), 300);
});

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    maxZoom: 18,
}).addTo(map);

// Layer group untuk marker supaya mudah di-clear
let markerLayer = L.layerGroup().addTo(map);

/**
 * Render marker pelabuhan ke peta.
 * Ini yang dimaksud "Marker Interaktif" — setiap marker bisa diklik
 * dan menampilkan popup dengan detail pelabuhan.
 */
function renderPorts(ports) {
    markerLayer.clearLayers();
    document.getElementById('mapMarkerCount').textContent = `${ports.length} marker`;

    ports.forEach(p => {
        if (!p.lat || !p.lng) return;

        // Marker interaktif — bisa diklik untuk popup
        const marker = L.circleMarker([p.lat, p.lng], {
            radius: 5,
            fillColor: '#0d6efd',
            color: '#fff',
            weight: 1,
            opacity: 1,
            fillOpacity: 0.8,
        });

        // Popup detail pelabuhan (ini yang dimaksud "interaktif")
        marker.bindPopup(`
            <div style="min-width:170px;font-family:'Inter',sans-serif;">
                <div style="display:flex;align-items:center;gap:6px;margin-bottom:6px;border-bottom:1px solid #eee;padding-bottom:6px;">
                    ${p.flag ? `<img src="${p.flag}" style="height:16px;border-radius:2px;">` : '⚓'}
                    <strong style="font-size:0.85rem;">${p.name}</strong>
                </div>
                <table style="width:100%;font-size:0.8rem;border-collapse:collapse;">
                    <tr>
                        <td style="color:#6c757d;padding:2px 0;width:60px;">Negara</td>
                        <td><strong>${p.country}</strong></td>
                    </tr>
                    <tr>
                        <td style="color:#6c757d;padding:2px 0;">Kode</td>
                        <td>${p.port_code}</td>
                    </tr>
                    <tr>
                        <td style="color:#6c757d;padding:2px 0;">Tipe</td>
                        <td>${p.harbor_type}</td>
                    </tr>
                    <tr>
                        <td style="color:#6c757d;padding:2px 0;">Koordinat</td>
                        <td style="font-size:0.75rem;">${p.lat.toFixed(4)}, ${p.lng.toFixed(4)}</td>
                    </tr>
                </table>
            </div>
        `, { maxWidth: 200 });

        // Tooltip ringan — muncul saat hover (tanpa klik)
        marker.bindTooltip(p.name, {
            permanent: false,
            direction: 'top',
            offset: [0, -8],
            className: 'leaflet-tooltip-port'
        });

        markerLayer.addLayer(marker);
    });

    if (ports.length === 0) {
        document.getElementById('searchResultInfo').textContent =
            '⚠️ Tidak ada pelabuhan ditemukan dengan filter ini.';
    }
}

// Render data awal
renderPorts(portData);

// Search by nama pelabuhan
function doSearch() {
    const q       = document.getElementById('portSearch').value.trim();
    const country = document.getElementById('countryFilter').value;

    if (!q && !country) {
        alert('Masukkan nama pelabuhan atau pilih negara.');
        return;
    }

    document.getElementById('searchResultInfo').textContent = 'Mencari...';

    let url = '/ports/search?';
    if (q)       url += `q=${encodeURIComponent(q)}&`;
    if (country) url += `country=${encodeURIComponent(country)}`;

    fetch(url, { headers: { 'Accept': 'application/json' } })
    .then(r => r.json())
    .then(data => {
        renderPorts(data);
        document.getElementById('searchResultInfo').textContent =
            `Ditemukan ${data.length} pelabuhan`;

        // Zoom ke hasil pertama
        if (data.length > 0 && data[0].lat && data[0].lng) {
            const zoomLevel = data.length === 1 ? 12 : data.length < 10 ? 6 : 4;
            map.flyTo([data[0].lat, data[0].lng], zoomLevel, { duration: 1 });
        }
    })
    .catch(() => {
        document.getElementById('searchResultInfo').textContent = 'Gagal mencari.';
    });
}

document.getElementById('btnSearch').addEventListener('click', doSearch);
document.getElementById('portSearch').addEventListener('keypress', e => {
    if (e.key === 'Enter') doSearch();
});

// Auto-search saat pilih negara dari dropdown
document.getElementById('countryFilter').addEventListener('change', function() {
    if (this.value) {
        document.getElementById('portSearch').value = '';
        doSearch();
    }
});

// Reset
document.getElementById('btnReset').addEventListener('click', () => {
    document.getElementById('portSearch').value = '';
    document.getElementById('countryFilter').value = '';
    document.getElementById('searchResultInfo').textContent = '500 sampel acak ditampilkan di peta';
    renderPorts(portData);
    map.flyTo([20, 0], 2, { duration: 1 });
});

// Auto-connect ke GlobalCountry
document.addEventListener('DOMContentLoaded', () => {
    const gc = GlobalCountry.get();
    if (gc.cca3 && gc.cca3 !== 'IDN') {
        const select = document.getElementById('countryFilter');
        const exists = Array.from(select.options).some(o => o.value === gc.cca3);
        if (exists) {
            select.value = gc.cca3;
            doSearch();
        }
    }
});
</script>

<style>
.leaflet-tooltip-port {
    background: rgba(0,0,0,0.75);
    color: #fff;
    border: none;
    font-size: 0.75rem;
    padding: 2px 6px;
    border-radius: 4px;
    box-shadow: none;
}
.leaflet-tooltip-port::before {
    border-top-color: rgba(0,0,0,0.75);
}
</style>
@endpush