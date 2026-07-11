@extends('layouts.app')

@section('title', 'Risk Scoring Engine')

@section('content')
<div class="row g-4">

    {{-- Header --}}
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1">Risk Scoring Engine</h4>
                <p class="text-muted mb-0">
                    Weighted Risk Model: Weather (30%) + Inflation (20%) + Currency (10%) + News Sentiment (40%)
                </p>
            </div>
            <span class="badge bg-primary fs-6">{{ $summary['total'] }} Negara</span>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="col-md-4">
        <div class="card border-0 shadow-sm" style="border-left: 4px solid #198754 !important;">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted small mb-1">Low Risk</p>
                    <h3 class="mb-0 text-success">{{ $summary['low'] }}</h3>
                    <small class="text-muted">Score 0 – 33</small>
                </div>
                <i class="bi bi-shield-check text-success" style="font-size: 2.5rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm" style="border-left: 4px solid #ffc107 !important;">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted small mb-1">Medium Risk</p>
                    <h3 class="mb-0 text-warning">{{ $summary['medium'] }}</h3>
                    <small class="text-muted">Score 34 – 66</small>
                </div>
                <i class="bi bi-shield-exclamation text-warning" style="font-size: 2.5rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm" style="border-left: 4px solid #dc3545 !important;">
            <div class="card-body d-flex justify-content-between align-items-center">
                <div>
                    <p class="text-muted small mb-1">High Risk</p>
                    <h3 class="mb-0 text-danger">{{ $summary['high'] }}</h3>
                    <small class="text-muted">Score 67 – 100</small>
                </div>
                <i class="bi bi-shield-x text-danger" style="font-size: 2.5rem; opacity: 0.3;"></i>
            </div>
        </div>
    </div>

    {{-- Chart Distribusi + Top 5 Berisiko --}}
    <div class="col-md-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3">
                <h6 class="mb-0">Distribusi Level Risiko</h6>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center">
                <canvas id="riskDistChart" style="max-height: 250px;"></canvas>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="row g-3 h-100">
            {{-- Top 5 Paling Berisiko --}}
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
                                        <img src="{{ $rs->country->flag_url }}" style="height: 18px; border-radius: 2px;">
                                    @endif
                                    <span class="small fw-semibold">{{ $rs->country?->name ?? '—' }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress" style="width: 80px; height: 6px;">
                                        <div class="progress-bar bg-danger"
                                            style="width: {{ $rs->total_score }}%"></div>
                                    </div>
                                    <span class="badge bg-danger small">{{ $rs->total_score }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Top 5 Paling Aman --}}
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
                                        <img src="{{ $rs->country->flag_url }}" style="height: 18px; border-radius: 2px;">
                                    @endif
                                    <span class="small fw-semibold">{{ $rs->country?->name ?? '—' }}</span>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress" style="width: 80px; height: 6px;">
                                        <div class="progress-bar bg-success"
                                            style="width: {{ $rs->total_score }}%"></div>
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
                <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
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
                                <tr class="risk-row" data-name="{{ strtolower($rs->country?->name ?? '') }}">
                                    <td class="text-muted small">{{ $i + 1 }}</td>
                                    <td>
                                        @if ($rs->country?->flag_url)
                                            <img src="{{ $rs->country->flag_url }}"
                                                style="height: 16px; border-radius: 2px; margin-right: 6px;">
                                        @endif
                                        {{ $rs->country?->name ?? '—' }}
                                    </td>
                                    <td class="text-center small">{{ $rs->weather_score }}</td>
                                    <td class="text-center small">{{ $rs->inflation_score }}</td>
                                    <td class="text-center small">{{ $rs->currency_score }}</td>
                                    <td class="text-center small">{{ $rs->news_score }}</td>
                                    <td class="text-center">
                                        <strong>{{ $rs->total_score }}</strong>
                                    </td>
                                    <td class="text-center">
                                        @php
                                            $badgeClass = match($rs->risk_level) {
                                                'Low Risk'    => 'bg-success',
                                                'Medium Risk' => 'bg-warning text-dark',
                                                'High Risk'   => 'bg-danger',
                                                default       => 'bg-secondary',
                                            };
                                        @endphp
                                        <span class="badge {{ $badgeClass }} small">{{ $rs->risk_level }}</span>
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
    // Chart distribusi level risiko
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
            plugins: {
                legend: { position: 'bottom' }
            }
        }
    });

    // Search tabel
    document.getElementById('searchCountry').addEventListener('input', function () {
        const keyword = this.value.toLowerCase();
        document.querySelectorAll('.risk-row').forEach(row => {
            const name = row.dataset.name;
            row.style.display = name.includes(keyword) ? '' : 'none';
        });
    });
</script>
@endpush