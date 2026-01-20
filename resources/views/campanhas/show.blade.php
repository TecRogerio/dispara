{{-- resources/views/campanhas/show.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="panel-header">
    <div>
        <h1 class="panel-title">Campanha: {{ $campaign->name }}</h1>
        <p class="panel-subtitle">
            Status: <strong>{{ $campaign->status ?? 'draft' }}</strong>
            @if($campaign->instance)
                • Instância: <strong>{{ $campaign->instance->instance_name ?? '—' }}</strong>
            @endif
        </p>
    </div>

    <div style="display:flex; gap:10px; flex-wrap:wrap;">
        <form method="POST" action="{{ route('campanhas.dispatch', $campaign->id) }}">
            @csrf
            <input type="hidden" name="limit" value="20">
            <button type="submit" class="z-btn z-btn-secondary">🚀 Disparar (manual)</button>
        </form>

        <form method="POST" action="{{ route('campanhas.dispatch.auto', $campaign->id) }}">
            @csrf
            <button type="submit" class="z-btn z-btn-primary">⚙️ Disparar (automático / fila)</button>
        </form>

        <a href="{{ route('campanhas.recipients', $campaign->id) }}" class="z-btn">📋 Destinatários</a>
        <a href="{{ route('campanhas.index') }}" class="z-btn">← Voltar</a>
    </div>
</div>

@if(session('success'))
    <div class="z-card" style="margin-bottom:14px;">
        <div class="z-card-body">
            <div class="z-badge z-badge-ok">✅ {{ session('success') }}</div>
        </div>
    </div>
@endif

@if(session('error'))
    <div class="z-card" style="margin-bottom:14px;">
        <div class="z-card-body">
            <div class="z-badge z-badge-off">⛔ {{ session('error') }}</div>
        </div>
    </div>
@endif

@if($errors->any())
    <div class="z-card" style="margin-bottom:14px;">
        <div class="z-card-body">
            <div class="z-badge z-badge-off">⛔ Corrija os erros abaixo</div>
            <ul style="margin-top:10px;">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

<div class="z-grid" style="display:grid; grid-template-columns: 1fr 1fr; gap:14px;">
    {{-- Importar destinatários --}}
    <div class="z-card">
        <div class="z-card-body">
            <h3 style="margin:0 0 10px 0;">📥 Importar destinatários</h3>

            <form method="POST" action="{{ route('campanhas.recipients.import', $campaign->id) }}" enctype="multipart/form-data">
                @csrf

                <label style="display:block; margin-bottom:6px;">Cole números (um por linha) ou “nome;telefone”</label>
                <textarea name="content" rows="6" class="z-input" style="width:100%;">{{ old('content') }}</textarea>

                <div style="display:flex; gap:12px; align-items:center; margin-top:10px; flex-wrap:wrap;">
                    <label style="display:flex; gap:6px; align-items:center;">
                        <input type="checkbox" name="ddi55" value="1" {{ old('ddi55') ? 'checked' : '' }}>
                        Adicionar DDI 55 automaticamente
                    </label>

                    <input type="file" name="file" class="z-input" />

                    <button type="submit" class="z-btn z-btn-primary">Importar</button>
                </div>

                <p style="margin-top:10px; opacity:.8;">
                    Dica: se a Evolution estiver no VPS, a mídia precisa estar numa URL pública acessível.
                </p>
            </form>
        </div>
    </div>

    {{-- Adicionar mensagens --}}
    <div class="z-card">
        <div class="z-card-body">
            <h3 style="margin:0 0 10px 0;">✉️ Mensagens da campanha</h3>

            <form method="POST" action="{{ route('campanhas.messages.store', $campaign->id) }}" enctype="multipart/form-data">
                @csrf

                <label style="display:block; margin-bottom:6px;">Texto (ou legenda se anexar arquivo)</label>
                <textarea name="text" rows="4" class="z-input" style="width:100%;">{{ old('text') }}</textarea>

                <div style="display:flex; gap:12px; align-items:center; margin-top:10px; flex-wrap:wrap;">
                    <input type="file" name="file" class="z-input" />
                    <button type="submit" class="z-btn z-btn-primary">Adicionar</button>
                </div>
            </form>

            <hr style="margin:14px 0; opacity:.2;">

            @php
                $msgs = $campaign->messages->sortBy('position');
            @endphp

            @if($msgs->count() === 0)
                <p style="opacity:.8;">Nenhuma mensagem cadastrada ainda.</p>
            @else
                <div style="display:flex; flex-direction:column; gap:10px;">
                    @foreach($msgs as $m)
                        <div class="z-card" style="border:1px solid rgba(0,0,0,.08);">
                            <div class="z-card-body">
                                <div style="display:flex; justify-content:space-between; gap:10px; align-items:flex-start;">
                                    <div>
                                        <div style="font-weight:700;">
                                            #{{ $m->position }} • tipo: {{ $m->primary_type ?? 'text' }}
                                        </div>

                                        @if(($m->primary_type ?? 'text') === 'text')
                                            <div style="white-space:pre-wrap; margin-top:6px;">{{ $m->text }}</div>
                                        @else
                                            <div style="margin-top:6px;">
                                                <div style="opacity:.8;">Legenda:</div>
                                                <div style="white-space:pre-wrap;">{{ $m->caption ?? $m->text }}</div>

                                                @if(!empty($m->media_url))
                                                    <div style="margin-top:8px;">
                                                        <a href="{{ $m->media_url }}" target="_blank">Abrir mídia</a>
                                                    </div>
                                                @endif
                                            </div>
                                        @endif
                                    </div>

                                    <form method="POST" action="{{ route('campanhas.messages.destroy', [$campaign->id, $m->id]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="z-btn" onclick="return confirm('Remover mensagem?')">🗑️</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Status AJAX --}}
<div class="z-card" style="margin-top:14px;">
    <div class="z-card-body">
        <h3 style="margin:0 0 10px 0;">📡 Progresso</h3>
        <div id="dispatchStatusBox" style="opacity:.9;">Carregando…</div>
    </div>
</div>

<script>
(async function poll() {
    const el = document.getElementById('dispatchStatusBox');
    const url = @json(route('campanhas.dispatch.status', $campaign->id));

    async function tick() {
        try {
            const r = await fetch(url, { headers: { 'Accept': 'application/json' } });
            const j = await r.json();
            el.innerHTML =
                `Status: <b>${j.status}</b> • Total: <b>${j.total}</b> • Enviados: <b>${j.sent}</b> • Falhas: <b>${j.failed}</b> • Pendentes: <b>${j.remaining}</b><br>` +
                `Hoje: <b>${j.sent_today}</b> / Limite: <b>${j.daily_limit}</b> • Cota restante: <b>${j.quota_left}</b>`;
        } catch (e) {
            el.textContent = 'Falha ao carregar status.';
        }
        setTimeout(tick, 3000);
    }
    tick();
})();
</script>
@endsection
