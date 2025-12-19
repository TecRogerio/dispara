@extends('layouts.auth')

@section('title', 'Recuperar senha')
@section('subtitle', 'Informe seu e-mail e enviaremos um link para redefinir sua senha.')

@section('content')
    <h2 class="title">Recuperar senha</h2>
    <p class="desc">Digite o e-mail cadastrado para receber o link de redefinição.</p>

    @if (session('status'))
        <div class="success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="field">
            <label for="email">E-mail</label>
            <input id="email" type="email" name="email"
                   class="input @error('email') invalid @enderror"
                   value="{{ old('email') }}" required autocomplete="email" autofocus>
            @error('email') <div class="error-text">{{ $message }}</div> @enderror
        </div>

        <button class="btn" type="submit">Enviar link de redefinição</button>

        <div class="footer">
            Lembrou a senha? <a class="link" href="{{ route('login') }}">Voltar para o login</a>
        </div>
    </form>
@endsection
