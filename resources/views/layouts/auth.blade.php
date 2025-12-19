<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Acesso')</title>

    <!-- Fonte -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Tema global -->
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">

    <style>
        .wrap{ min-height: 100vh; display:flex; }

        .left{
            width: 30%;
            min-width: 360px;
            max-width: 520px;
            padding: 42px 38px;
            display:flex;
            flex-direction: column;
            justify-content: center;
            border-right: 1px solid var(--border);
            background: rgba(15,23,42,.55);
            backdrop-filter: blur(10px);
        }

        .right{
            flex: 1;
            position: relative;
            overflow: hidden;
        }

        .right::before{
            content:"";
            position:absolute;
            inset:0;
            background:
                linear-gradient(120deg, rgba(6,11,22,.85), rgba(6,11,22,.25)),
                url("{{ asset('images/login-bg.jpg') }}");
            background-size: cover;
            background-position: center;
            filter: saturate(1.05);
        }

        .right::after{
            content:"";
            position:absolute;
            inset:0;
            background:
                radial-gradient(900px 380px at 20% 15%, rgba(79,70,229,.25), transparent 65%),
                radial-gradient(900px 380px at 75% 85%, rgba(124,58,237,.22), transparent 65%);
            pointer-events:none;
        }

        .brand{
            display:flex;
            align-items:center;
            gap:10px;
            margin-bottom: 18px;
        }
        .logo{
            width:40px; height:40px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary), var(--primary2));
            box-shadow: 0 12px 35px rgba(79,70,229,.25);
        }
        .brand h1{
            font-size: 16px;
            margin:0;
            letter-spacing: .2px;
        }

        .subtitle{
            margin: 0 0 26px 0;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.4;
        }

        .card{
            border: 1px solid var(--border);
            border-radius: 18px;
            padding: 22px;
            background: rgba(11,18,32,.65);
            box-shadow: 0 20px 60px rgba(0,0,0,.35);
        }

        .title{
            margin: 0 0 6px 0;
            font-size: 22px;
            font-weight: 700;
        }
        .desc{
            margin: 0 0 18px 0;
            color: var(--muted);
            font-size: 13px;
            line-height: 1.45;
        }

        .field{
            display:flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 14px;
        }
        label{
            font-size: 12px;
            color: var(--muted);
        }

        .input{
            height: 44px;
            padding: 0 12px;
            border-radius: 12px;
            border: 1px solid var(--border);
            background: rgba(2,6,23,.55);
            color: var(--text);
            outline: none;
            transition: border .15s ease, box-shadow .15s ease;
        }
        .input:focus{
            border-color: rgba(79,70,229,.65);
            box-shadow: 0 0 0 4px rgba(79,70,229,.15);
        }

        .btn{
            width:100%;
            height: 46px;
            border:0;
            border-radius: 12px;
            cursor: pointer;
            color: white;
            font-weight: 600;
            letter-spacing: .2px;
            background: linear-gradient(135deg, var(--primary), var(--primary2));
            box-shadow: 0 18px 40px rgba(79,70,229,.22);
            transition: transform .08s ease, filter .15s ease;
        }
        .btn:active{ transform: translateY(1px); }
        .btn:hover{ filter: brightness(1.06); }

        .invalid{
            border-color: rgba(239,68,68,.55) !important;
            box-shadow: 0 0 0 4px rgba(239,68,68,.12) !important;
        }
        .error-text{
            margin-top: 6px;
            color: #fecaca;
            font-size: 12px;
        }

        .errors{
            margin: 0 0 12px 0;
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid rgba(239,68,68,.35);
            background: rgba(239,68,68,.10);
            color: #fecaca;
            font-size: 12px;
            line-height: 1.35;
        }

        .success{
            margin: 0 0 12px 0;
            padding: 10px 12px;
            border-radius: 12px;
            border: 1px solid rgba(34,197,94,.25);
            background: rgba(34,197,94,.10);
            color: #bbf7d0;
            font-size: 12px;
            line-height: 1.35;
        }

        .footer{
            margin-top: 14px;
            font-size: 12px;
            color: var(--muted);
            text-align:center;
        }
        .link{
            color: #c7d2fe;
            text-decoration: none;
            font-size: 12px;
        }
        .link:hover{ text-decoration: underline; }

        @media (max-width: 980px){
            .wrap{ flex-direction: column; }
            .left{
                width:100%;
                max-width: none;
                min-width: unset;
                border-right:0;
                border-bottom:1px solid var(--border);
            }
            .right{ min-height: 44vh; }
        }
    </style>

    @stack('styles')
</head>
<body>

<div class="wrap">
    <div class="left">
        <div class="brand">
            <div class="logo"></div>
            <h1>Agendei ZAP</h1>
        </div>

        <p class="subtitle">@yield('subtitle', 'Acesse sua conta para continuar.')</p>

        <div class="card">
            @yield('content')
        </div>
    </div>

    <div class="right" aria-hidden="true"></div>
</div>

@stack('scripts')
</body>
</html>
