<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Global Supply Chain Risk Intelligence Platform')</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0f2027 0%, #203a43 50%, #2c5364 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .auth-card {
            width: 100%;
            max-width: 420px;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 2.5rem;
        }
        .auth-brand {
            font-weight: 700;
            font-size: 1.1rem;
            color: #203a43;
            margin-bottom: 0.25rem;
        }
        .auth-subtitle {
            color: #6c757d;
            font-size: 0.85rem;
            margin-bottom: 1.75rem;
        }
        .btn-primary {
            background-color: #203a43;
            border-color: #203a43;
        }
        .btn-primary:hover {
            background-color: #0f2027;
            border-color: #0f2027;
        }
    </style>
</head>
<body>
    <div class="auth-card">
        <div class="auth-brand">🌐 Global Supply Chain Risk Intelligence</div>
        <div class="auth-subtitle">@yield('subtitle', 'Silakan masuk untuk melanjutkan')</div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>