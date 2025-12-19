@extends('layouts.auth')

@section('title', 'Redefinir senha')
@section('subtitle', 'Defina uma nova senha para sua conta.')

@section('content')
    <h2 class="title">Redefinir senha</h2>
    <p class="desc">Informe seu e-mail e crie uma nova senha segura.</p>

    <form method="POST" action="{{ route('password.update') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $token }}">

        <div class="field">
            <label for="email">E-mail</label>
            <input id="email" type="email" name="email"
                   class="input @error('email') invalid @enderror"
                   value="{{ $email ?? old('email') }}" required autocomplete="email" autofocus>
            @error('email') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="password">Nova senha</label>
            <input id="password" type="password" name="password"
                   class="input @error('password') invalid @enderror"
                   required autocomplete="new-password">
            @error('password') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="password-confirm">Confirmar nova senha</label>
            <input id="password-confirm" type="password" name="password_confirmation"
                   class="input" required autocomplete="new-password">
        </div>

        <button class="btn" type="submit">Redefinir senha</button>

        <div class="footer">
            <a class="link" href="{{ route('login') }}">Voltar para o login</a>
        </div>
    </form>
@endsection
