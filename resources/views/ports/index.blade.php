@extends('layouts.app')

@section('title', 'Port Dashboard')

@section('content')
<div class="row g-4">

    {{-- Header --}}
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1">Port Location Dashboard</h4>
                <p class="text-muted mb-0">
                    Data pelabuhan dari World Port Index (NGIA) — {{ number_format($totalPorts) }} pelabuhan di {{ $totalCountries }} negara
                </p>
            </div>
            <span class="badge bg-primary fs-6">{{ number_format($totalPorts) }} Pelabuhan</span>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #0d6efd !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Total Pelabuhan</p>
                        <h2 class="mb-0 text-primary fw-bold">{{ number_format($totalPorts) }}</h2>
                        <small class="text-muted">di seluruh dunia</small>
                    </div>
                    <i class="bi bi-geo-alt-fill text-primary" style="font-size: 2.5rem; opacity: 0.2;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #198754 !important;">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <p class="text-muted small mb-1">Negara</p>
                        <h2 class="mb-0 text-success fw-bold">{{ $totalCountries }}</h2>
                        <small class="text-muted">memiliki pelabuhan</small>
                    </div>
                    <i class="bi bi-globe text-success" style="font-size: 2.5rem; opacity: 0.2;"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="mb-0">Top 5 Negara dengan Pelabuhan Terbanyak</h6>
            </div>
            <div class="card-body py-2">
                @foreach ($topCountries as $country)
                    <div class="d-flex justify-content-between align-items-center py-1">
                        <div class="d-flex align-items-center gap-2">
                            @if ($country->flag_url)
                                <img src="{{ $country->flag_url }}" style="height: 16px; border-radius: 2px;">
                            @endif
                            <span class="small">{{ $country->name }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="progress" style="width: 80px; height: 6px;">
                                <div class="progress-bar bg-primary"
                                    style="width: {{ min(($country->ports_count / $countries->first()->ports_count) * 100, 100) }}%">
                                </div>
                            </div>
                            <span class="badge bg-light text-dark border small">{{ $country->ports_count }}</span>
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
                    <div class="col-md-5">
                        <input type="text" id="portSearch" class="form-control form-control-sm"
                            placeholder="Cari nama pelabuhan... (tekan Enter atau klik Cari)">
                    </div>
                    <div class="col-md-auto">
                        <button id="btnSearch" class="btn btn-primary btn-sm">
                            <i class="bi bi-search me-1"></i>Cari
                        </button>
                        <button id="btnReset" class="btn btn-outline-secondary btn-sm ms-1">
                            Reset
                        </button>
                    </div>
                    <div class="col-md-auto ms-auto">
                        <small class="text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            Peta menampilkan 500 sampel acak dari {{ number_format($totalPorts) }} pelabuhan
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Peta Leaflet --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div id="portMap" style="height: 550px; border-radius: 0 0 12px 12px;"></div>
            </div>
        </div>
    </div>

    {{-- Harbor Type Stats --}}
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

const map = L.map('portMap', {
    center: [20, 0],
    zoom: 2,
});

setTimeout(() => map.invalidateSize(), 100);

L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    maxZoom: 18,
}).addTo(map);

let markerLayer = L.layerGroup().addTo(map);

function renderPorts(ports) {
    markerLayer.clearLayers();

    ports.forEach(p => {
        if (!p.lat || !p.lng) return;

        const marker = L.circleMarker([p.lat, p.lng], {
            radius: 5,
            fillColor: '#0d6efd',
            color: '#fff',
            weight: 1,
            opacity: 1,
            fillOpacity: 0.8,
        });

        marker.bindPopup(`
            <div style="min-width: 160px; font-family: 'Inter', sans-serif;">
                <div style="display: flex; align-items: center; gap: 6px; margin-bottom: 6px;">
                    ${p.flag ? `<img src="${p.flag}" style="height: 16px; border-radius: 2px;">` : ''}
                    <strong style="font-size: 0.9rem;">${p.name}</strong>
                </div>
                <table style="width: 100%; font-size: 0.82rem;">
                    <tr>
                        <td style="color: #6c757d;">Negara</td>
                        <td><strong>${p.country}</strong></td>
                    </tr>
                    <tr>
                        <td style="color: #6c757d;">Kode</td>
                        <td>${p.port_code}</td>
                    </tr>
                    <tr>
                        <td style="color: #6c757d;">Tipe</td>
                        <td>${p.harbor_type}</td>
                    </tr>
                </table>
            </div>
        `);

        markerLayer.addLayer(marker);
    });
}

// Render data awal
renderPorts(portData);

// Search
document.getElementById('btnSearch').addEventListener('click', doSearch);
document.getElementById('portSearch').addEventListener('keypress', e => {
    if (e.key === 'Enter') doSearch();
});

function doSearch() {
    const q = document.getElementById('portSearch').value.trim();
    if (!q) return;

    fetch(`/ports/search?q=${encodeURIComponent(q)}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        renderPorts(data);
        // Auto-filter ke negara saat ini
document.addEventListener('DOMContentLoaded', () => {
    const gc = GlobalCountry.get();
    const select = document.getElementById('countryFilter');
    if (select && gc.cca3) {
        const exists = Array.from(select.options).some(o => o.value === gc.cca3);
        if (exists) {
            select.value = gc.cca3;
            doSearch();
        }
    }
});
        if (data.length > 0 && data[0].lat && data[0].lng) {
            map.setView([data[0].lat, data[0].lng], 6);
        }
    })
    .catch(err => console.error('Search error:', err));
}

// Reset
document.getElementById('btnReset').addEventListener('click', () => {
    document.getElementById('portSearch').value = '';
    renderPorts(portData);
    map.setView([20, 0], 2);
});
</script>
@endpush