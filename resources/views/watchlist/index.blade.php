@extends('layouts.app')

@section('title', 'Favorite Monitoring')

@section('content')
<div class="row g-4">

    {{-- Header --}}
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1">Favorite Monitoring List</h4>
                <p class="text-muted mb-0">Pantau negara-negara pilihan kamu dalam satu halaman</p>
            </div>
            <span class="badge bg-primary fs-6">{{ $watchlists->count() }} Negara</span>
        </div>
    </div>

    {{-- Toast Notifikasi --}}
    <div class="position-fixed top-0 end-0 p-3" style="z-index: 9999;">
        <div id="toastMsg" class="toast align-items-center border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body fw-semibold" id="toastText">—</div>
                <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>

    {{-- Form Tambah Negara --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="mb-3"><i class="bi bi-plus-circle me-2 text-primary"></i>Tambah Negara ke Favorit</h6>
                <div class="row g-2 align-items-end">
                    <div class="col-md-6">
                        <select id="addCountrySelect" class="form-select">
                            <option value="">-- Pilih Negara --</option>
                            @foreach ($countries as $c)
                                <option value="{{ $c->cca3 }}">
                                    {{ $c->name }} ({{ $c->cca3 }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-auto">
                        <button id="btnAddWatchlist" class="btn btn-primary">
                            <i class="bi bi-star-fill me-2"></i>Tambah ke Favorit
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Daftar Watchlist --}}
    @if ($watchlists->isEmpty())
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-star text-warning" style="font-size: 3rem;"></i>
                    <h5 class="mt-3">Belum Ada Negara Favorit</h5>
                    <p class="text-muted">Tambahkan negara yang ingin kamu pantau menggunakan form di atas.</p>
                </div>
            </div>
        </div>
    @else
        <div class="col-12" id="watchlistContainer">
            <div class="row g-3">
                @foreach ($watchlists as $wl)
                    @php
                        $country  = $wl->country;
                        $economic = $country->latestEconomicData;
                        $weather  = $country->weatherCache;
                        $risk     = $country->riskScore;
                        $riskClass = match($risk?->risk_level) {
                            'Low Risk'    => 'success',
                            'Medium Risk' => 'warning',
                            'High Risk'   => 'danger',
                            default       => 'secondary',
                        };
                    @endphp
                    <div class="col-md-6 col-lg-4 watchlist-card" id="card-{{ $country->cca3 }}">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-2">
                                    @if ($country->flag_url)
                                        <img src="{{ $country->flag_url }}" style="height: 24px; border-radius: 2px;">
                                    @endif
                                    <h6 class="mb-0">{{ $country->name }}</h6>
                                </div>
                                <div class="d-flex gap-1 align-items-center">
                                    @if ($risk)
                                        <span class="badge bg-{{ $riskClass }}">{{ $risk->risk_level }}</span>
                                    @endif
                                    <button class="btn btn-outline-danger btn-sm py-0 px-2 btn-remove"
                                        data-cca3="{{ $country->cca3 }}"
                                        data-name="{{ $country->name }}"
                                        title="Hapus dari favorit">
                                        <i class="bi bi-x-lg"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row g-2 text-center">
                                    <div class="col-6">
                                        <p class="text-muted small mb-0">GDP</p>
                                        <strong class="small">
                                            @if ($economic?->gdp)
                                                {{ number_format($economic->gdp / 1e12, 2) }}T USD
                                            @else
                                                N/A
                                            @endif
                                        </strong>
                                    </div>
                                    <div class="col-6">
                                        <p class="text-muted small mb-0">Inflasi</p>
                                        <strong class="small">
                                            {{ $economic?->inflation_rate ? number_format($economic->inflation_rate, 2).'%' : 'N/A' }}
                                        </strong>
                                    </div>
                                    <div class="col-6">
                                        <p class="text-muted small mb-0">Populasi</p>
                                        <strong class="small">
                                            {{ $economic?->population ? number_format($economic->population / 1e6, 1).'M' : 'N/A' }}
                                        </strong>
                                    </div>
                                    <div class="col-6">
                                        <p class="text-muted small mb-0">Risk Score</p>
                                        <strong class="small text-{{ $riskClass }}">
                                            {{ $risk?->total_score ?? 'N/A' }}
                                        </strong>
                                    </div>
                                </div>

                                @if ($weather)
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted">
                                            <i class="bi bi-thermometer-half me-1"></i>{{ $weather->temperature }}°C
                                            <i class="bi bi-wind ms-2 me-1"></i>{{ $weather->wind_speed }} km/h
                                        </small>
                                        <span class="badge bg-{{ $weather->storm_risk === 'high' ? 'danger' : ($weather->storm_risk === 'medium' ? 'warning text-dark' : 'success') }} small">
                                            {{ strtoupper($weather->storm_risk) }}
                                        </span>
                                    </div>
                                @endif
                            </div>
                            <div class="card-footer bg-white border-0 pt-0">
                                <a href="{{ route('countries.index') }}?cca3={{ $country->cca3 }}"
                                    class="btn btn-outline-primary btn-sm w-100">
                                    <i class="bi bi-eye me-1"></i>Lihat Detail
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
const toast = new bootstrap.Toast(document.getElementById('toastMsg'));

function showToast(message, type = 'success') {
    const toastEl = document.getElementById('toastMsg');
    const toastText = document.getElementById('toastText');
    toastEl.className = `toast align-items-center border-0 text-white bg-${type}`;
    toastText.textContent = message;
    toast.show();
}

// Tambah negara ke watchlist
document.getElementById('btnAddWatchlist').addEventListener('click', function () {
    const select = document.getElementById('addCountrySelect');
    const cca3   = select.value;
    const name   = select.selectedOptions[0]?.text || '';

    if (!cca3) {
        showToast('Pilih negara terlebih dahulu.', 'warning');
        return;
    }

    this.disabled = true;
    this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Menambahkan...';

    fetch('/watchlist', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        },
        body: JSON.stringify({ cca3 }),
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, 'success');
            setTimeout(() => window.location.reload(), 1500);
        } else {
            showToast(data.message, 'warning');
        }
    })
    .catch(() => showToast('Terjadi kesalahan. Coba lagi.', 'danger'))
    .finally(() => {
        this.disabled = false;
        this.innerHTML = '<i class="bi bi-star-fill me-2"></i>Tambah ke Favorit';
    });
});

// Hapus dari watchlist
document.querySelectorAll('.btn-remove').forEach(btn => {
    btn.addEventListener('click', function () {
        const cca3 = this.dataset.cca3;
        const name = this.dataset.name;

        if (!confirm(`Hapus ${name} dari daftar favorit?`)) return;

        fetch('/watchlist', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                    || '{{ csrf_token() }}',
            },
            body: JSON.stringify({ cca3 }),
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                document.getElementById(`card-${cca3}`)?.remove();
            } else {
                showToast(data.message, 'danger');
            }
        })
        .catch(() => showToast('Terjadi kesalahan.', 'danger'));
    });
});
</script>
@endpush