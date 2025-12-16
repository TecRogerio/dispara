<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'AgendeiZap CRM') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito:400,600,700,800&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <style>
        :root{
            --az-bg: #0b1220;
            --az-surface: #ffffff;
            --az-border: rgba(15, 23, 42, .10);
            --az-muted: rgba(15, 23, 42, .62);
            --az-text: #0f172a;
            --az-primary: #2563eb;
            --az-primary-soft: rgba(37, 99, 235, .10);
            --az-success: #16a34a;
            --az-danger: #dc2626;

            --az-radius: 16px;
            --az-radius-sm: 12px;
            --az-shadow: 0 10px 24px rgba(15, 23, 42, .08);
            --az-shadow-sm: 0 6px 16px rgba(15, 23, 42, .06);
        }

        body{
            font-family: Nunito, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            background: radial-gradient(1200px 600px at 10% 0%, rgba(37,99,235,.10), transparent 60%),
                        radial-gradient(900px 500px at 90% 10%, rgba(16,185,129,.08), transparent 60%),
                        #f5f7fb;
            color: var(--az-text);
        }

        /* Topbar */
        .az-topbar{
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(15,23,42,.06);
        }
        .az-brand{
            display:flex;
            align-items:center;
            gap:10px;
            text-decoration:none;
            font-weight: 900;
            letter-spacing: .2px;
            color: var(--az-text);
        }
        .az-brand:hover{ color: var(--az-text); opacity: .95; }
        .az-logo{
            width: 34px;
            height: 34px;
            border-radius: 12px;
            background: radial-gradient(circle at 30% 30%, rgba(255,255,255,.9), rgba(255,255,255,.2)),
                        linear-gradient(135deg, #2563eb, #22c55e);
            box-shadow: 0 8px 18px rgba(37, 99, 235, .18);
        }
        .az-chip{
            font-size: 12px;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 999px;
            color: #0b1220;
            background: rgba(15,23,42,.06);
            border: 1px solid rgba(15,23,42,.08);
        }

        /* Layout com sidebar */
        .app-shell {
            display: flex;
            gap: 18px;
            align-items: flex-start;
        }
        .app-sidebar {
            width: 260px;
            flex: 0 0 260px;
        }
        .app-content {
            flex: 1;
            min-width: 0;
        }

        /* Sidebar card */
        .sidebar-card {
            background: var(--az-surface);
            border: 1px solid var(--az-border);
            border-radius: var(--az-radius);
            box-shadow: var(--az-shadow-sm);
            padding: 14px;
            position: sticky;
            top: 86px; /* abaixo da navbar */
        }

        .sidebar-title {
            font-weight: 900;
            font-size: 12px;
            color: rgba(15,23,42,.55);
            text-transform: uppercase;
            letter-spacing: .08em;
            margin: 10px 0 8px;
        }

        .sidebar-company {
            display:flex;
            align-items:center;
            gap:10px;
            font-weight: 900;
            font-size: 15px;
            margin-bottom: 10px;
        }
        .sidebar-avatar{
            width: 34px;
            height: 34px;
            border-radius: 12px;
            background: rgba(37,99,235,.12);
            border: 1px solid rgba(37,99,235,.18);
            display:flex;
            align-items:center;
            justify-content:center;
            font-weight: 900;
            color: #1d4ed8;
            user-select:none;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            padding: 10px 12px;
            border-radius: 12px;
            color: var(--az-text);
            font-weight: 800;
            border: 1px solid transparent;
            transition: all .15s ease;
        }
        .sidebar-link:hover {
            background: var(--az-primary-soft);
            color: var(--az-primary);
            border-color: rgba(37,99,235,.18);
            transform: translateY(-1px);
        }
        .sidebar-link.active {
            background: rgba(37,99,235,.12);
            color: var(--az-primary);
            border-color: rgba(37,99,235,.22);
        }

        .sidebar-icon{
            width: 18px;
            height: 18px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            opacity: .9;
        }

        .sidebar-sep {
            margin: 12px 0;
            border-top: 1px solid rgba(15,23,42,.08);
        }

        .az-surface{
            background: var(--az-surface);
            border: 1px solid var(--az-border);
            border-radius: var(--az-radius);
            box-shadow: var(--az-shadow);
        }

        /* Responsivo */
        @media (max-width: 992px) {
            main.py-4{ padding-top: 14px !important; }
            .app-shell { flex-direction: column; }
            .app-sidebar { width: 100%; flex: 0 0 auto; }
            .sidebar-card { position: relative; top: auto; }

            .sidebar-links-grid{
                display:grid;
                grid-template-columns: 1fr 1fr;
                gap:10px;
            }
            .sidebar-link{
                justify-content:center;
                text-align:center;
                padding: 12px 10px;
            }
        }

        @media (max-width: 520px){
            .sidebar-links-grid{ grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div id="app">

    <!-- Topbar -->
    <nav class="navbar navbar-expand-md navbar-light bg-white az-topbar shadow-sm">
        <div class="container">
            <a class="az-brand" href="{{ url('/') }}">
                <span class="az-logo" aria-hidden="true"></span>
                <span>{{ config('app.name', 'AgendeiZap') }}</span>
                <span class="az-chip">CRM</span>
            </a>

            <button class="navbar-toggler" type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#navbarSupportedContent"
                    aria-controls="navbarSupportedContent"
                    aria-expanded="false"
                    aria-label="{{ __('Toggle navigation') }}">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav me-auto"></ul>

                <ul class="navbar-nav ms-auto">
                    @guest
                        @if (Route::has('login'))
                            <li class="nav-item">
                                <a class="nav-link fw-bold" href="{{ route('login') }}">{{ __('Login') }}</a>
                            </li>
                        @endif

                        @if (Route::has('register'))
                            <li class="nav-item">
                                <a class="nav-link fw-bold" href="{{ route('register') }}">{{ __('Register') }}</a>
                            </li>
                        @endif
                    @else
                        <li class="nav-item dropdown">
                            <a id="navbarDropdown" class="nav-link dropdown-toggle fw-bold"
                               href="#" role="button"
                               data-bs-toggle="dropdown"
                               aria-haspopup="true"
                               aria-expanded="false"
                               v-pre>
                                {{ Auth::user()->name }}
                            </a>

                            <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="{{ route('logout') }}"
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    {{ __('Logout') }}
                                </a>

                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>

    <main class="py-4">
        <div class="container">

            @auth
                @php
                    $u = Auth::user();

                    $name = trim((string)($u->name ?? ''));
                    $initials = $name !== '' ? mb_strtoupper(mb_substr($name, 0, 1)) : 'U';

                    // Regra tolerante de "admin" (troque depois por Gate/Policy quando tiver)
                    $isAdmin = false;
                    try {
                        if (isset($u->is_admin) && (int)$u->is_admin === 1) $isAdmin = true;
                        if (!$isAdmin && isset($u->role) && (string)$u->role === 'admin') $isAdmin = true;
                        if (!$isAdmin && isset($u->email) && is_string($u->email) && str_ends_with($u->email, '@agendeizap.com.br')) $isAdmin = true;
                    } catch (\Throwable $e) {
                        $isAdmin = false;
                    }

                    $hasAdminDashboardRoute = \Illuminate\Support\Facades\Route::has('admin.dashboard');
                @endphp

                <div class="app-shell">
                    <!-- Sidebar -->
                    <aside class="app-sidebar">
                        <div class="sidebar-card">

                            <div class="sidebar-title">Conta</div>
                            <div class="sidebar-company">
                                <div class="sidebar-avatar" aria-hidden="true">{{ $initials }}</div>
                                <div style="min-width:0;">
                                    <div style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                        {{ $u->name }}
                                    </div>
                                    <div style="font-size:12px;color: rgba(15,23,42,.55); font-weight:700;">
                                        Painel AgendeiZap
                                    </div>
                                </div>
                            </div>

                            <div class="sidebar-title">Menu</div>

                            <div class="sidebar-links-grid">
                                <a class="sidebar-link {{ request()->routeIs('campanhas.*') ? 'active' : '' }}"
                                   href="{{ route('campanhas.index') }}">
                                    <span class="sidebar-icon" aria-hidden="true">📣</span>
                                    <span>Campanhas</span>
                                </a>

                                <a class="sidebar-link {{ request()->routeIs('instancias.*') ? 'active' : '' }}"
                                   href="{{ route('instancias.index') }}">
                                    <span class="sidebar-icon" aria-hidden="true">📱</span>
                                    <span>Instâncias</span>
                                </a>

                                {{-- Admin: só mostra se rota existe + usuário for admin --}}
                                @if($hasAdminDashboardRoute && $isAdmin)
                                    <a class="sidebar-link {{ request()->routeIs('admin.*') ? 'active' : '' }}"
                                       href="{{ route('admin.dashboard') }}">
                                        <span class="sidebar-icon" aria-hidden="true">🧭</span>
                                        <span>Painel</span>
                                    </a>
                                @endif

                                <a class="sidebar-link"
                                   href="{{ route('logout') }}"
                                   onclick="event.preventDefault(); document.getElementById('logout-form-sidebar').submit();">
                                    <span class="sidebar-icon" aria-hidden="true">🚪</span>
                                    <span>Sair</span>
                                </a>
                            </div>

                            <form id="logout-form-sidebar" action="{{ route('logout') }}" method="POST" class="d-none">
                                @csrf
                            </form>

                            <div class="sidebar-sep"></div>

                            <div style="font-size:12px;color: rgba(28, 53, 110, 0.55); font-weight:700; line-height:1.4;">
                                Interface otimizada para uso rápido e responsivo.
                            </div>

                        </div>
                    </aside>

                    <!-- Conteúdo -->
                    <section class="app-content">
                        @yield('content')
                    </section>
                </div>
            @else
                {{-- Visitante (login/register) sem sidebar --}}
                @yield('content')
            @endauth

        </div>
    </main>

</div>
</body>
</html>
