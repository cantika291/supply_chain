@extends('layouts.app')

@section('title', 'Overview')

@section('content')
    <h4 class="mb-1">Selamat datang, {{ auth()->user()->name }} 👋</h4>
    <p class="text-muted mb-4">Berikut ringkasan akses cepat ke seluruh modul platform.</p>

    <div class="row g-3">
        @php
            $menus = [
                ['route' => 'countries.index', 'icon' => 'bi-globe-asia-australia', 'title' => 'Country Dashboard', 'desc' => 'GDP, inflasi, populasi, cuaca per negara'],
                ['route' => 'risk.index', 'icon' => 'bi-shield-exclamation', 'title' => 'Risk Scoring', 'desc' => 'Skor risiko rantai pasok tiap negara'],
                ['route' => 'weather.index', 'icon' => 'bi-cloud-lightning-rain-fill', 'title' => 'Weather Monitoring', 'desc' => 'Peta cuaca ekstrem global'],
                ['route' => 'currency.index', 'icon' => 'bi-currency-exchange', 'title' => 'Currency Dashboard', 'desc' => 'Kurs & tren mata uang'],
                ['route' => 'news.index', 'icon' => 'bi-newspaper', 'title' => 'News Intelligence', 'desc' => 'Berita ekonomi & sentiment analysis'],
                ['route' => 'ports.index', 'icon' => 'bi-geo-alt-fill', 'title' => 'Port Dashboard', 'desc' => 'Lokasi pelabuhan dunia'],
                ['route' => 'comparison.index', 'icon' => 'bi-bar-chart-steps', 'title' => 'Country Comparison', 'desc' => 'Bandingkan 2 negara sekaligus'],
                ['route' => 'watchlist.index', 'icon' => 'bi-star-fill', 'title' => 'Favorite Monitoring', 'desc' => 'Negara yang kamu pantau'],
            ];
        @endphp

        @foreach ($menus as $menu)
            <div class="col-md-6 col-lg-3">
                <a href="{{ route($menu['route']) }}" class="text-decoration-none">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <i class="bi {{ $menu['icon'] }} mb-2" style="font-size: 1.75rem; color: #2c8a5e;"></i>
                            <h6 class="card-title text-dark">{{ $menu['title'] }}</h6>
                            <p class="card-text text-muted small mb-0">{{ $menu['desc'] }}</p>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
@endsection