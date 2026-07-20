<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - Supply Chain Risk Platform</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-width: 260px;
            --brand-dark: #0f2027;
            --brand-mid: #203a43;
            --brand-accent: #2c8a5e;
        }
        body { font-family: 'Inter', sans-serif; background-color: #f4f6f8; }
        .sidebar {
            position: fixed; top: 0; left: 0; height: 100vh;
            width: var(--sidebar-width);
            background: linear-gradient(180deg, var(--brand-dark) 0%, var(--brand-mid) 100%);
            overflow-y: auto; z-index: 1030; transition: transform 0.25s ease;
        }
        .sidebar-brand {
            color: #fff; font-weight: 700; font-size: 1.05rem;
            padding: 1.25rem 1.25rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
        }
        .sidebar-brand small {
            display: block; font-weight: 400; font-size: 0.7rem;
            color: rgba(255,255,255,0.55); margin-top: 0.15rem;
        }
        .sidebar .nav-section-title {
            color: rgba(255,255,255,0.4); font-size: 0.7rem;
            text-transform: uppercase; letter-spacing: 0.05em;
            padding: 1rem 1.25rem 0.4rem;
        }
        .sidebar .nav-link {
            color: rgba(255,255,255,0.75); font-size: 0.9rem;
            padding: 0.6rem 1.25rem; display: flex; align-items: center;
            gap: 0.65rem; border-left: 3px solid transparent;
            transition: all 0.15s ease;
        }
        .sidebar .nav-link:hover { background: rgba(255,255,255,0.06); color: #fff; }
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.1); color: #fff;
            border-left-color: var(--brand-accent); font-weight: 600;
        }
        .sidebar .nav-link i { font-size: 1rem; width: 1.25rem; text-align: center; }
        .main-wrapper {
            margin-left: var(--sidebar-width); min-height: 100vh;
            display: flex; flex-direction: column;
        }
        .topbar {
            background: #fff; border-bottom: 1px solid #e5e7eb;
            padding: 0.65rem 1.5rem; display: flex; align-items: center;
            justify-content: space-between;
        }
        .content-area { padding: 1.75rem; flex: 1; }
        .btn-sidebar-toggle { display: none; }

        /* Global Country Badge */
        .global-country-badge {
            background: linear-gradient(135deg, #0f2027, #203a43);
            color: #fff; border-radius: 20px; padding: 4px 12px;
            font-size: 0.8rem; display: flex; align-items: center; gap: 6px;
            cursor: pointer; border: none; transition: opacity 0.2s;
        }
        .global-country-badge:hover { opacity: 0.85; }
        .global-country-badge .flag-img { height: 16px; border-radius: 2px; }

        /* Live Badge */
        .live-badge {
            display: inline-flex; align-items: center; gap: 5px;
            background: rgba(25, 135, 84, 0.1); color: #198754;
            border: 1px solid rgba(25, 135, 84, 0.3); border-radius: 20px;
            padding: 2px 10px; font-size: 0.72rem; font-weight: 600;
        }
        .live-dot {
            width: 7px; height: 7px; background: #198754;
            border-radius: 50%; flex-shrink: 0;
            animation: livePulse 1.5s infinite;
        }
        @keyframes livePulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.4; transform: scale(0.8); }
        }

        /* Pagination fix */
        .pagination .page-link svg { width: 12px; height: 12px; }
        .pagination { font-size: 0.875rem; }

        @media (max-width: 991.98px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.show { transform: translateX(0); }
            .main-wrapper { margin-left: 0; }
            .btn-sidebar-toggle { display: inline-flex; }
        }
    </style>
</head>
<body>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        🌐 Supply Chain Risk
        <small>Intelligence Platform</small>
    </div>
    <nav class="nav flex-column pb-4">
        <div class="nav-section-title">Utama</div>
        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2-fill"></i> Overview
        </a>
        <a href="{{ route('countries.index') }}" class="nav-link {{ request()->routeIs('countries.*') ? 'active' : '' }}">
            <i class="bi bi-globe-asia-australia"></i> Country Dashboard
        </a>
        <a href="{{ route('risk.index') }}" class="nav-link {{ request()->routeIs('risk.*') ? 'active' : '' }}">
            <i class="bi bi-shield-exclamation"></i> Risk Scoring
        </a>

        <div class="nav-section-title">Data & Analitik</div>
        <a href="{{ route('weather.index') }}" class="nav-link {{ request()->routeIs('weather.*') ? 'active' : '' }}">
            <i class="bi bi-cloud-lightning-rain-fill"></i> Weather Monitoring
        </a>
        <a href="{{ route('currency.index') }}" class="nav-link {{ request()->routeIs('currency.*') ? 'active' : '' }}">
            <i class="bi bi-currency-exchange"></i> Currency Dashboard
        </a>
        <a href="{{ route('news.index') }}" class="nav-link {{ request()->routeIs('news.*') ? 'active' : '' }}">
            <i class="bi bi-newspaper"></i> News Intelligence
        </a>
        <a href="{{ route('ports.index') }}" class="nav-link {{ request()->routeIs('ports.*') ? 'active' : '' }}">
            <i class="bi bi-geo-alt-fill"></i> Port Dashboard
        </a>
        <a href="{{ route('dataviz.index') }}" class="nav-link {{ request()->routeIs('dataviz.*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-line-fill"></i> Data Visualization
        </a>

        <div class="nav-section-title">Alat Bantu</div>
        <a href="{{ route('comparison.index') }}" class="nav-link {{ request()->routeIs('comparison.*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-steps"></i> Country Comparison
        </a>
        <a href="{{ route('watchlist.index') }}" class="nav-link {{ request()->routeIs('watchlist.*') ? 'active' : '' }}">
            <i class="bi bi-star-fill"></i> Favorite Monitoring
        </a>

        @if (auth()->user()->isAdmin())
            <div class="nav-section-title">Administrasi</div>
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.*') ? 'active' : '' }}">
                <i class="bi bi-gear-fill"></i> Admin Panel
            </a>
        @endif
    </nav>
</aside>

<div class="main-wrapper">
    <header class="topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-outline-secondary btn-sm btn-sidebar-toggle" id="sidebarToggle">
                <i class="bi bi-list"></i>
            </button>
            <h6 class="mb-0 text-muted">@yield('title', 'Dashboard')</h6>
        </div>

        <div class="d-flex align-items-center gap-2">

            {{-- Live Badge --}}
            <span class="live-badge" title="Data diperbarui otomatis oleh scheduler">
                <span class="live-dot"></span>
                LIVE
            </span>

            {{-- Global Country Badge --}}
            <button class="global-country-badge" id="globalCountryBadge"
                onclick="window.location='/countries'"
                title="Klik untuk ganti negara aktif">
                <i class="bi bi-globe2"></i>
                <img class="flag-img d-none" id="globalFlagImg" src="" alt="">
                <span id="globalCountryName">Pilih Negara</span>
                <i class="bi bi-pencil-fill" style="font-size:0.65rem;opacity:0.7;"></i>
            </button>

            <span class="text-muted small">
                {{ auth()->user()->name }}
                <span class="badge bg-secondary ms-1">{{ auth()->user()->role }}</span>
            </span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-danger btn-sm">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </button>
            </form>
        </div>
    </header>

    <main class="content-area">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
        @yield('content')
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // ============================================================
    // GLOBAL COUNTRY CONTEXT
    // ============================================================
    window.GlobalCountry = {
        get: function() {
            return {
                cca3:     localStorage.getItem('gc_cca3')     || 'IDN',
                name:     localStorage.getItem('gc_name')     || 'Indonesia',
                flag:     localStorage.getItem('gc_flag')     || '',
                currency: localStorage.getItem('gc_currency') || 'IDR',
            };
        },
        set: function(cca3, name, flag, currency) {
            localStorage.setItem('gc_cca3',     cca3);
            localStorage.setItem('gc_name',     name);
            localStorage.setItem('gc_flag',     flag     || '');
            localStorage.setItem('gc_currency', currency || '');
            this.updateBadge();
        },
        updateBadge: function() {
            const data    = this.get();
            const nameEl  = document.getElementById('globalCountryName');
            const flagEl  = document.getElementById('globalFlagImg');
            if (nameEl) nameEl.textContent = data.name;
            if (flagEl) {
                if (data.flag) {
                    flagEl.src = data.flag;
                    flagEl.classList.remove('d-none');
                } else {
                    flagEl.classList.add('d-none');
                }
            }
        }
    };

    document.addEventListener('DOMContentLoaded', () => {
        GlobalCountry.updateBadge();
    });

    document.getElementById('sidebarToggle')?.addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('show');
    });

    // ============================================================
    // SIDEBAR SCROLL POSITION PERSISTENCE
    // Supaya posisi scroll sidebar tidak reset ke atas tiap kali
    // pindah halaman (karena tiap klik menu = full page reload)
    // ============================================================
    (function () {
        const SIDEBAR_SCROLL_KEY = 'sidebarScrollTop';
        const sidebar = document.getElementById('sidebar');
        if (!sidebar) return;

        // 1. Restore posisi scroll begitu halaman baru selesai dirender
        const savedScroll = sessionStorage.getItem(SIDEBAR_SCROLL_KEY);
        if (savedScroll !== null) {
            sidebar.scrollTop = parseInt(savedScroll, 10);
        }

        // 2. Simpan posisi tiap kali user scroll manual
        sidebar.addEventListener('scroll', function () {
            sessionStorage.setItem(SIDEBAR_SCROLL_KEY, sidebar.scrollTop);
        });

        // 3. Simpan juga tepat sebelum link di sidebar diklik
        //    (jaga-jaga kalau halaman langsung pindah sebelum event scroll sempat jalan)
        sidebar.querySelectorAll('a.nav-link').forEach(function (link) {
            link.addEventListener('click', function () {
                sessionStorage.setItem(SIDEBAR_SCROLL_KEY, sidebar.scrollTop);
            });
        });
    })();
</script>
@stack('scripts')
</body>
</html>