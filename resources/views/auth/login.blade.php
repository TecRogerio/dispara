@extends('layouts.auth')

@section('title', 'Login')
@section('subtitle', 'Acesse sua conta para gerenciar campanhas e contatos com praticidade.')

@section('content')
    <h2 class="title">Entrar</h2>
    <p class="desc">Use seu e-mail e senha para continuar.</p>

    @if ($errors->any())
        <div class="errors">
            @foreach ($errors->all() as $error)
                <div>• {{ $error }}</div>
            @endforeach
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <div class="field">
            <label for="email">E-mail</label>
            <input class="input" id="email" type="email" name="email"
                   value="{{ old('email') }}" required autofocus autocomplete="username">
        </div>

        <div class="field">
            <label for="password">Senha</label>
            <input class="input" id="password" type="password" name="password"
                   required autocomplete="current-password">
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;gap:10px;margin:10px 0 16px;">
            <label style="display:flex;align-items:center;gap:8px;color:var(--muted);font-size:12px;">
                <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                Manter conectado
            </label>

            @if (Route::has('password.request'))
                <a class="link" href="{{ route('password.request') }}">Esqueci minha senha</a>
            @endif
        </div>

        <button class="btn" type="submit">Acessar</button>

        <div class="footer">
            Não tem conta? <a class="link" href="{{ route('register') }}">Cadastre-se</a>
        </div>
    </form>
@endsection
