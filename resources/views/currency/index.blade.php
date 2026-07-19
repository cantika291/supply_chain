@extends('layouts.app')
@section('title', 'Currency Dashboard')
@section('content')
<div class="row g-4">

    {{-- Header --}}
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h4 class="mb-1">Currency Impact Dashboard</h4>
                <p class="text-muted mb-0">Nilai tukar mata uang global relatif terhadap USD</p>
                @if($lastSync)
                    <small class="text-muted">
                        <i class="bi bi-clock me-1"></i>
                        Terakhir diperbarui: <strong>{{ $lastSync }}</strong>
                    </small>
                @endif
            </div>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-outline-success btn-sm" id="btnRefreshCurrency">
                    <i class="bi bi-arrow-clockwise me-1"></i>Refresh Kurs
                </button>
                <span class="badge bg-primary fs-6">{{ $latestRates->count() }} Mata Uang</span>
            </div>
        </div>
    </div>

    {{-- Featured Currency Cards --}}
    @foreach ($featuredRates as $code => $rate)
        <div class="col-md-3" id="card-{{ $code }}">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted small mb-1">{{ $code }} / USD</p>
                            <h4 class="mb-0 rate-value-{{ $code }}">
                                {{ number_format($rate->rate_to_usd, in_array($code, ['JPY','IDR']) ? 2 : 4) }}
                            </h4>
                            {{-- Tampilkan HANYA tanggal, tanpa jam --}}
                            <small class="text-muted rate-date-{{ $code }}">
                                Per 1 USD · {{ \Carbon\Carbon::parse($rate->rate_date)->format('d M Y') }}
                            </small>
                        </div>
                        <span class="badge bg-light text-dark border fs-6">{{ $code }}</span>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    {{-- Grafik Tren Kurs --}}
    <div class="col-md-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>Tren Kurs Mata Uang</h6>
                <select id="currencySelect" class="form-select form-select-sm w-auto">
                    @foreach ($currencies as $code)
                        <option value="{{ $code }}" {{ $code === 'IDR' ? 'selected' : '' }}>
                            {{ $code }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="card-body">
                <canvas id="currencyTrendChart" style="max-height:300px;"></canvas>
                <p class="text-muted small text-center mt-2 mb-0" id="chartInfo">
                    Menampilkan tren kurs IDR terhadap USD
                </p>
            </div>
        </div>
    </div>

    {{-- Detail Kurs Terpilih --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="mb-0"><i class="bi bi-info-circle me-2"></i>Detail Kurs Terpilih</h6>
            </div>
            <div class="card-body text-center d-flex flex-column align-items-center justify-content-center">
                <div class="display-6 fw-bold text-primary mb-2" id="selectedRate">—</div>
                <p class="text-muted mb-1" id="selectedCode">Pilih mata uang di dropdown</p>
                <p class="text-muted small" id="selectedDate">—</p>
                <hr class="w-100">
                <p class="small text-muted mb-1">Artinya:</p>
                <p class="fw-semibold" id="selectedMeaning">—</p>
            </div>
        </div>
    </div>

    {{-- Tabel Semua Kurs --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 pt-3 d-flex justify-content-between align-items-center">
                <h6 class="mb-0">Semua Nilai Tukar (per 1 USD)</h6>
                <input type="text" id="searchCurrency" class="form-control form-control-sm w-auto"
                    placeholder="Cari kode mata uang...">
            </div>
            <div class="card-body p-0">
                <div class="table-responsive" style="max-height:400px;overflow-y:auto;">
                    <table class="table table-hover table-sm mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th>Kode</th>
                                <th>Negara</th>
                                <th class="text-end">Kurs</th>
                                <th class="text-end">Tanggal</th>
                                <th class="text-center">Grafik</th>
                            </tr>
                        </thead>
                        <tbody id="ratesTableBody">
                            @foreach ($latestRates as $rate)
                                @php
                                    $country = $countries->firstWhere('currency_code', $rate->currency_code);
                                @endphp
                                <tr class="rate-row" data-code="{{ $rate->currency_code }}">
                                    <td>
                                        @if ($country?->flag_url)
                                            <img src="{{ $country->flag_url }}"
                                                style="height:14px;margin-right:4px;border-radius:2px;">
                                        @endif
                                        <strong>{{ $rate->currency_code }}</strong>
                                    </td>
                                    <td class="text-muted small">{{ $country?->name ?? '—' }}</td>
                                    <td class="text-end">{{ number_format($rate->rate_to_usd, 4) }}</td>
                                    {{-- Tanggal tanpa jam --}}
                                    <td class="text-end text-muted small">
                                        {{ \Carbon\Carbon::parse($rate->rate_date)->format('d M Y') }}
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-outline-primary btn-sm py-0 px-2 btn-chart"
                                            data-code="{{ $rate->currency_code }}">
                                            <i class="bi bi-graph-up"></i>
                                        </button>
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
let trendChart = null;

document.addEventListener('DOMContentLoaded', () => {
    const gc = GlobalCountry.get();
    const code = gc.currency || 'IDR';
    const select = document.getElementById('currencySelect');
    const exists = Array.from(select.options).some(o => o.value === code);
    if (exists) select.value = code;
    setTimeout(() => loadCurrencyChart(code), 300);
});

document.getElementById('currencySelect').addEventListener('change', function() {
    loadCurrencyChart(this.value);
});

document.querySelectorAll('.btn-chart').forEach(btn => {
    btn.addEventListener('click', function() {
        const code = this.dataset.code;
        document.getElementById('currencySelect').value = code;
        loadCurrencyChart(code);
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
});

document.getElementById('searchCurrency').addEventListener('input', function() {
    const kw = this.value.toLowerCase();
    document.querySelectorAll('.rate-row').forEach(row => {
        row.style.display = row.dataset.code.toLowerCase().includes(kw) ? '' : 'none';
    });
});

function loadCurrencyChart(code) {
    fetch(`/currency/history?code=${code}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(r => r.json())
    .then(data => {
        renderChart(data);
        updateDetailCard(data);
    })
    .catch(err => console.error('Error:', err));
}

function renderChart(data) {
    const labels = data.history.map(h => {
        // Format tanggal tanpa jam
        const d = new Date(h.date);
        return d.toLocaleDateString('id-ID', { day:'2-digit', month:'short', year:'numeric' });
    });
    const rates = data.history.map(h => parseFloat(h.rate));

    if (trendChart) trendChart.destroy();

    trendChart = new Chart(document.getElementById('currencyTrendChart'), {
        type: 'line',
        data: {
            labels,
            datasets: [{
                label: `${data.code} / USD`,
                data: rates,
                borderColor: '#0d6efd',
                backgroundColor: 'rgba(13,110,253,0.08)',
                borderWidth: 2,
                pointRadius: rates.length > 10 ? 2 : 5,
                fill: true,
                tension: 0.3,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: ctx => `1 USD = ${ctx.parsed.y.toLocaleString()} ${data.code}`
                    }
                }
            },
            scales: {
                y: { ticks: { callback: val => val.toLocaleString() } }
            }
        }
    });

    document.getElementById('chartInfo').textContent =
        `Tren kurs ${data.code} terhadap USD (${labels.length} titik data)`;
}

function updateDetailCard(data) {
    const rate = parseFloat(data.latest);
    document.getElementById('selectedRate').textContent =
        rate.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 4 });
    document.getElementById('selectedCode').textContent = `${data.code} per 1 USD`;
    // Format tanggal tanpa jam
    const d = new Date(data.date);
    document.getElementById('selectedDate').textContent =
        'Data per ' + d.toLocaleDateString('id-ID', { day:'2-digit', month:'long', year:'numeric' });
    document.getElementById('selectedMeaning').textContent =
        `1 USD = ${rate.toLocaleString()} ${data.code}`;
}

// ============================================================
// REFRESH KURS — diperbaiki
// ============================================================
document.getElementById('btnRefreshCurrency')?.addEventListener('click', function() {
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Memperbarui...';

    fetch('/currency/refresh', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        }
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            // Reload halaman untuk tampilkan data terbaru
            window.location.reload();
        } else {
            alert('⚠️ ' + data.message);
            btn.disabled = false;
            btn.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i>Refresh Kurs';
        }
    })
    .catch(() => {
        alert('Gagal refresh. Coba lagi.');
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i>Refresh Kurs';
    });
});
</script>
@endpush