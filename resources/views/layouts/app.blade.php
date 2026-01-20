<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'AgendeiZap CRM') }}</title>

    <!-- Bootstrap/Laravel UI (base) -->
    {{-- Mix: garante cache-busting e caminho correto --}}
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">

    <!-- Tema (dark padrão do projeto) - vem depois para sobrescrever o bootstrap -->
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    <link rel="stylesheet" href="{{ asset('css/panel.css') }}">

    <!-- Scripts -->
    {{-- Mix: garante cache-busting e caminho correto --}}
    <script src="{{ mix('js/app.js') }}" defer></script>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito:400,600,700,800&display=swap" rel="stylesheet">

    <style>
        body{
            font-family: Nunito, system-ui, -apple-system, Segoe UI, Roboto, Helvetica, Arial, sans-serif;
            background: radial-gradient(1200px 600px at 20% 10%, rgba(79,70,229,.22), transparent 60%),
                        radial-gradient(800px 500px at 70% 80%, rgba(124,58,237,.18), transparent 60%),
                        #060b16;
            color: var(--text);
        }

        .az-topbar{
            background: rgba(15,23,42,.55) !important;
            backdrop-filter: blur(10px);
            border-bottom: 1px solid var(--border);
        }
        .az-brand{
            display:flex;
            align-items:center;
            gap:10px;
            text-decoration:none;
            font-weight: 900;
            letter-spacing: .2px;
            color: var(--text);
        }
        .az-brand:hover{ color: var(--text); opacity: .95; }

        .az-logo{
            width: 34px;
            height: 34px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary), var(--primary2));
            box-shadow: 0 12px 35px rgba(79,70,229,.25);
        }
        .az-chip{
            font-size: 12px;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 999px;
            color: var(--text);
            background: rgba(2,6,23,.35);
            border: 1px solid var(--border);
        }

        .navbar .nav-link{
            color: rgba(229,231,235,.86) !important;
        }
        .navbar .nav-link:hover{
            color: var(--text) !important;
        }

        .dropdown-menu{
            background: rgba(11,18,32,.95);
            border: 1px solid var(--border);
            border-radius: 14px;
            box-shadow: 0 16px 50px rgba(0,0,0,.35);
        }
        .dropdown-item{
            color: rgba(229,231,235,.92);
            font-weight: 700;
        }
        .dropdown-item:hover{
            background: rgba(79,70,229,.10);
            color: var(--text);
        }

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

        .sidebar-card {
            border: 1px solid var(--border);
            border-radius: 16px;
            background: rgba(11,18,32,.65);
            box-shadow: 0 16px 50px rgba(0,0,0,.25);
            padding: 14px;
            position: sticky;
            top: 86px;
        }

        .sidebar-title {
            font-weight: 900;
            font-size: 12px;
            color: rgba(148,163,184,.78);
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
            background: rgba(79,70,229,.14);
            border: 1px solid rgba(79,70,229,.22);
            display:flex;
            align-items:center;
            justify-content:center;
            font-weight: 900;
            color: #c7d2fe;
            user-select:none;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            padding: 10px 12px;
            border-radius: 12px;
            color: rgba(229,231,235,.92);
            font-weight: 800;
            border: 1px solid transparent;
            transition: all .15s ease;
        }
        .sidebar-link:hover {
            background: rgba(79,70,229,.10);
            color: #e0e7ff;
            border-color: rgba(79,70,229,.22);
            transform: translateY(-1px);
        }
        .sidebar-link.active {
            background: rgba(79,70,229,.14);
            color: #e0e7ff;
            border-color: rgba(79,70,229,.28);
        }

        .sidebar-icon{
            width: 18px;
            height: 18px;
            display:inline-flex;
            align-items:center;
            justify-content:center;
            opacity: .95;
        }

        .sidebar-sep {
            margin: 12px 0;
            border-top: 1px solid rgba(148,163,184,.16);
        }

        .navbar-toggler{
            border-color: rgba(148,163,184,.25) !important;
        }
        .navbar-toggler-icon{
            filter: invert(1) opacity(.85);
        }

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
    <nav class="navbar navbar-expand-md az-topbar">
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
                                    <div style="font-size:12px;color: rgba(148,163,184,.78); font-weight:700;">
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

                                <a class="sidebar-link {{ request()->routeIs('chats.*') ? 'active' : '' }}"
                                   href="{{ route('chats.index') }}">
                                    <span class="sidebar-icon" aria-hidden="true">💬</span>
                                    <span>Conversas</span>
                                </a>

                                <a class="sidebar-link {{ request()->routeIs('instancias.*') ? 'active' : '' }}"
                                   href="{{ route('instancias.index') }}">
                                    <span class="sidebar-icon" aria-hidden="true">📱</span>
                                    <span>Instâncias</span>
                                </a>

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

                            <div style="font-size:12px;color: rgba(148,163,184,.78); font-weight:700; line-height:1.4;">
                                Interface otimizada para uso rápido e responsivo.
                            </div>

                        </div>
                    </aside>

                    <section class="app-content">
                        <div class="panel-wrap">
                            <div class="panel-container">
                                @yield('content')
                            </div>
                        </div>
                    </section>
                </div>
            @else
                @yield('content')
            @endauth

        </div>
    </main>

</div>

{{-- ✅ IMPORTANTÍSSIMO para o @push('scripts') funcionar --}}
@stack('scripts')

</body>
</html>
