@extends('layouts.auth')

@section('title', 'Criar conta')
@section('subtitle', 'Crie sua conta para começar a usar o sistema.')

@section('content')
    <h2 class="title">Criar conta</h2>
    <p class="desc">Preencha os dados abaixo para finalizar seu cadastro.</p>

    <form method="POST" action="{{ route('register') }}">
        @csrf

        <div class="field">
            <label for="name">Nome</label>
            <input id="name" type="text" name="name"
                   class="input @error('name') invalid @enderror"
                   value="{{ old('name') }}" required autocomplete="name" autofocus>
            @error('name') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="email">E-mail</label>
            <input id="email" type="email" name="email"
                   class="input @error('email') invalid @enderror"
                   value="{{ old('email') }}" required autocomplete="email">
            @error('email') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="password">Senha</label>
            <input id="password" type="password" name="password"
                   class="input @error('password') invalid @enderror"
                   required autocomplete="new-password">
            @error('password') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <div class="field">
            <label for="password-confirm">Confirmar senha</label>
            <input id="password-confirm" type="password" name="password_confirmation"
                   class="input" required autocomplete="new-password">
        </div>

        <button class="btn" type="submit">Cadastrar</button>

        <div class="footer">
            Já tem conta? <a class="link" href="{{ route('login') }}">Entrar</a>
        </div>
    </form>
@endsection
