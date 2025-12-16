@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 860px; margin-top: 30px;">
    <div style="background:#fff;border-radius:14px;padding:28px;box-shadow:0 6px 18px rgba(0,0,0,.06);">

        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
            <h2 style="margin:0;font-weight:700;">Envio simples</h2>
            <a href="{{ route('instancias.index') }}" style="font-weight:600;">← Voltar</a>
        </div>

        @if (session('success'))
            <div style="margin-top:16px;padding:12px 14px;border-radius:10px;background:#e8fff1;border:1px solid #b8f1cc;color:#145a32;">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div style="margin-top:16px;padding:12px 14px;border-radius:10px;background:#ffecec;border:1px solid #ffbdbd;color:#7a1b1b;">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div style="margin-top:16px;padding:12px 14px;border-radius:10px;background:#fff3cd;border:1px solid #ffe69c;color:#6b4f00;">
                <ul style="margin:0;padding-left:18px;">
                    @foreach ($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('mensagens.enviar.post') }}" style="margin-top:18px;">
            @csrf

            <label style="display:block;margin-top:10px;font-weight:600;">Instância</label>
            <select name="whatsapp_instance_id" required style="width:100%;padding:10px;border:1px solid #ddd;border-radius:10px;">
                <option value="">Selecione…</option>
                @foreach ($instances as $i)
                    <option value="{{ $i->id }}" {{ old('whatsapp_instance_id') == $i->id ? 'selected' : '' }}>
                        {{ $i->label }} ({{ $i->instance_name }}) — limite {{ (int)($i->daily_limit ?? 200) }}/dia
                    </option>
                @endforeach
            </select>

            <label style="display:block;margin-top:10px;font-weight:600;">Telefone</label>
            <input type="text" name="to" value="{{ old('to') }}" placeholder="Ex: 54984359885" required
                   style="width:100%;padding:10px;border:1px solid #ddd;border-radius:10px;">

            <label style="display:flex;align-items:center;gap:8px;margin-top:10px;">
                <input type="checkbox" name="auto55" value="1" {{ old('auto55', '1') == '1' ? 'checked' : '' }}>
                Inserir +55 automaticamente (salva só dígitos: 55 + número)
            </label>

            <label style="display:block;margin-top:10px;font-weight:600;">Mensagem</label>
            <textarea name="message" rows="5" required
                      style="width:100%;padding:10px;border:1px solid #ddd;border-radius:10px;">{{ old('message') }}</textarea>

            <div style="margin-top:14px;display:flex;gap:10px;flex-wrap:wrap;">
                <button type="submit"
                        style="padding:10px 14px;border-radius:10px;border:1px solid #cfe2ff;background:#e7f1ff;cursor:pointer;font-weight:600;">
                    Enviar
                </button>
            </div>
        </form>

        <div style="margin-top:16px;color:#666;font-size:13px;">
            Dica: primeiro conecte a instância (QR) em <strong>Instâncias</strong>. Envio pode falhar se a instância estiver desconectada.
        </div>

    </div>
</div>
@endsection
