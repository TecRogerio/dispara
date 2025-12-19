@extends('layouts.app')

@section('content')

@php
    $status = $campaign->status ?? 'draft';

    // Badge do tema (sem bootstrap)
    $statusBadge = 'z-badge';
    if ($status === 'running') $statusBadge .= ' z-badge-warn';
    elseif ($status === 'finished') $statusBadge .= ' z-badge-ok';
    elseif ($status === 'failed') $statusBadge .= ' z-badge-off';
    elseif ($status === 'paused') $statusBadge .= ' z-badge-warn';

    $instanceLabel = optional($campaign->instance)->label ?? optional($campaign->instance)->instance_name ?? '-';

    // Resumo destinatários
    $recipients = $campaign->recipients ?? collect();
    $totalRecipients = $recipients->count();
    $validRecipients   = $recipients->where('is_valid', 1)->count();
    $invalidRecipients = $totalRecipients - $validRecipients;

    $pendingRecipients = $recipients->whereIn('status', ['pending'])->count();
    $sentRecipients    = $recipients->whereIn('status', ['sent'])->count();
    $failedRecipients  = $recipients->whereIn('status', ['failed'])->count();
@endphp

<div class="panel-header">
    <div>
        <h1 class="panel-title" style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
            <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width: 720px;">
                Campanha: {{ $campaign->name }}
            </span>
            <span class="{{ $statusBadge }}">{{ $status }}</span>
        </h1>

        <p class="panel-subtitle" style="margin-top:6px;line-height:1.6;">
            <span style="margin-right:10px;"><strong style="color:rgba(229,231,235,.92);">ID:</strong> {{ $campaign->id }}</span>
            <span style="margin-right:10px;">
                <strong style="color:rgba(229,231,235,.92);">Instância:</strong>
                {{ $instanceLabel }}
                @if(optional($campaign->instance)->instance_name)
                    <span style="color:var(--muted);">({{ $campaign->instance->instance_name }})</span>
                @endif
            </span>
            <span style="margin-right:10px;">
                <strong style="color:rgba(229,231,235,.92);">Delay:</strong>
                {{ (int)($campaign->delay_min_seconds ?? 1) }}s → {{ (int)($campaign->delay_max_seconds ?? 1) }}s
            </span>
            <span>
                <strong style="color:rgba(229,231,235,.92);">Criada:</strong>
                {{ optional($campaign->created_at)->format('d/m/Y H:i') ?? '-' }}
            </span>
        </p>
    </div>

    <div style="display:flex;gap:10px;flex-wrap:wrap;">
        <a href="{{ route('campanhas.index') }}" class="z-btn">← Voltar</a>
        <a href="{{ route('campanhas.create') }}" class="z-btn z-btn-primary">+ Nova campanha</a>
    </div>
</div>

{{-- Feedback --}}
@if ($errors->any())
    <div class="z-card" style="margin-bottom:14px;">
        <div class="z-card-body">
            <div class="z-badge z-badge-off" style="margin-bottom:10px;">⛔ Atenção: verifique os campos</div>
            <ul style="margin:0;padding-left:18px;color:#fecaca;font-size:13px;line-height:1.5;">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif

@if (session('success'))
    <div class="z-card" style="margin-bottom:14px;">
        <div class="z-card-body">
            <div class="z-badge z-badge-ok">✅ {{ session('success') }}</div>
        </div>
    </div>
@endif

@if (session('error'))
    <div class="z-card" style="margin-bottom:14px;">
        <div class="z-card-body">
            <div class="z-badge z-badge-off">⛔ {{ session('error') }}</div>
        </div>
    </div>
@endif

{{-- Resumo de destinatários --}}
<div class="z-card" style="margin-bottom:14px;">
    <div class="z-card-header">
        <strong>Destinatários (resumo)</strong>
        <div style="display:flex;gap:10px;flex-wrap:wrap;">
            <a href="{{ route('campanhas.recipients', $campaign->id) }}" class="z-btn">
                👥 Ver destinatários
            </a>

            <form method="POST"
                  action="{{ route('campanhas.recipients.dedup', $campaign->id) }}"
                  onsubmit="return confirm('Remover contatos duplicados por número nesta campanha? (mantém apenas 1 por número)');"
                  style="margin:0;">
                @csrf
                <button type="submit" class="z-btn">
                    🧹 Remover duplicados
                </button>
            </form>
        </div>
    </div>

    <div class="z-card-body">
        <div style="font-size:12px;color:var(--muted);margin-bottom:10px;">
            Para visualizar nomes e números carregados, use “Ver destinatários”.
        </div>

        <div style="display:flex;flex-wrap:wrap;gap:10px;">
            <span class="z-badge">Total: {{ $totalRecipients }}</span>
            <span class="z-badge z-badge-ok">Válidos: {{ $validRecipients }}</span>
            <span class="z-badge z-badge-off">Inválidos: {{ $invalidRecipients }}</span>
            <span class="z-badge z-badge-warn">Pendentes: {{ $pendingRecipients }}</span>
            <span class="z-badge z-badge-ok">Enviados: {{ $sentRecipients }}</span>
            <span class="z-badge z-badge-off">Falharam: {{ $failedRecipients }}</span>
        </div>
    </div>
</div>

{{-- Layout em colunas --}}
<div class="row g-3">

    {{-- 1) Importar destinatários --}}
    <div class="col-12 col-lg-6">
        <div class="z-card" style="height:100%;">
            <div class="z-card-header">
                <div>
                    <strong>1) Importar / Colar destinatários</strong>
                    <div style="font-size:12px;color:var(--muted);margin-top:4px;">
                        Aceita TXT/CSV ou colar lista (um por linha).
                    </div>
                </div>
                <a href="{{ route('campanhas.recipients', $campaign->id) }}" class="z-btn">
                    Ver lista
                </a>
            </div>

            <div class="z-card-body">
                <form method="POST"
                      action="{{ route('campanhas.recipients.import', $campaign->id) }}"
                      enctype="multipart/form-data"
                      style="display:grid;gap:12px;">
                    @csrf

                    <label style="display:flex;gap:10px;align-items:flex-start;color:rgba(229,231,235,.92);font-size:13px;">
                        <input type="checkbox" name="ddi55" value="1" checked id="ddi55" style="margin-top:3px;">
                        <span>
                            <strong>Inserir DDI +55 automaticamente</strong> (quando vier só DDD+número)
                            <div style="color:var(--muted);font-size:12px;margin-top:2px;">
                                Ex: “54912345678” vira “55 54 912345678”
                            </div>
                        </span>
                    </label>

                    <div class="field" style="margin-bottom:0;">
                        <label for="content">Cole aqui (um por linha) ou “nome;telefone”</label>
                        <textarea id="content" name="content" rows="7" class="z-input" style="height:auto;padding:10px 12px;" placeholder="Ex:
Maria; 54 91234-5678
João, 54991234567
54912345678">{{ old('content') }}</textarea>
                        <div style="font-size:12px;color:var(--muted);margin-top:6px;">
                            Dica: “nome;telefone” ou “nome,telefone”.
                        </div>
                    </div>

                    <div class="field" style="margin-bottom:0;">
                        <label for="file">Ou envie arquivo (.txt / .csv)</label>
                        <input id="file" type="file" name="file" class="z-input" style="padding-top:8px;" accept=".txt,.csv">
                    </div>

                    <div style="display:flex;gap:10px;flex-wrap:wrap;">
                        <button type="submit" class="z-btn z-btn-primary">Importar</button>
                        <a href="{{ route('campanhas.show', $campaign->id) }}" class="z-btn">Recarregar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- 2) Mensagens --}}
    <div class="col-12 col-lg-6">
        <div class="z-card" style="height:100%;">
            <div class="z-card-header">
                <div>
                    <strong>2) Mensagens da campanha</strong>
                    <div style="font-size:12px;color:var(--muted);margin-top:4px;">
                        (por enquanto só texto)
                    </div>
                </div>
            </div>

            <div class="z-card-body">
                <form method="POST" action="{{ route('campanhas.messages.store', $campaign->id) }}" style="display:grid;gap:10px;">
                    @csrf

                    <div class="field" style="margin-bottom:0;">
                        <label for="text">Mensagem que será enviada no WhatsApp</label>
                        <textarea id="text" name="text" rows="4" class="z-input" style="height:auto;padding:10px 12px;" placeholder="Digite a mensagem...">{{ old('text') }}</textarea>
                    </div>

                    <div style="display:flex;gap:10px;flex-wrap:wrap;">
                        <button type="submit" class="z-btn">+ Adicionar mensagem</button>
                    </div>
                </form>

                <div style="border-top:1px solid rgba(148,163,184,.16); margin:14px 0;"></div>

                <div>
                    @if(isset($campaign->messages) && $campaign->messages->count() > 0)
                        <div style="font-weight:900;margin-bottom:10px;">Mensagens cadastradas</div>

                        <div style="display:grid;gap:10px;">
                            @foreach($campaign->messages as $m)
                                <div class="z-card" style="box-shadow:none;">
                                    <div class="z-card-body">
                                        <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;align-items:flex-start;">
                                            <div style="min-width:220px;">
                                                <div style="font-weight:900;color:rgba(229,231,235,.92);margin-bottom:6px;">
                                                    #{{ (int)($m->position ?? 0) }}
                                                </div>
                                                <div style="white-space:pre-wrap;color:rgba(229,231,235,.92);">
                                                    {{ $m->text }}
                                                </div>
                                            </div>

                                            <form method="POST"
                                                  action="{{ route('campanhas.messages.destroy', [$campaign->id, $m->id]) }}"
                                                  onsubmit="return confirm('Remover esta mensagem?');"
                                                  style="margin:0;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="z-btn z-btn-danger">
                                                    Remover
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div style="color:var(--muted);">Nenhuma mensagem cadastrada ainda.</div>
                    @endif
                </div>

            </div>
        </div>
    </div>

    {{-- 3) Disparo --}}
    <div class="col-12">
        <div class="z-card">
            <div class="z-card-header">
                <div>
                    <strong>3) Disparar mensagens</strong>
                    <div style="font-size:12px;color:var(--muted);margin-top:4px;">
                        Executa em lote por clique (recomendado)
                    </div>
                </div>

                <span class="z-badge z-badge-ok">✅ Lote por clique</span>
            </div>

            <div class="z-card-body">
                <form method="POST" action="{{ route('campanhas.dispatch', $campaign->id) }}"
                      class="row g-2 align-items-end" style="margin:0;">
                    @csrf

                    <div class="col-12 col-md-4">
                        <label for="limit" style="font-size:12px;color:var(--muted);font-weight:800;margin-bottom:6px;display:block;">
                            Limite por clique
                        </label>
                        <input id="limit" type="number" name="limit" class="z-input" value="20" min="1" max="50">
                        <div style="font-size:12px;color:var(--muted);margin-top:6px;">Máximo: 50</div>
                    </div>

                    <div class="col-12 col-md-4">
                        <button type="submit" class="z-btn z-btn-primary" style="width:100%;justify-content:center;">
                            🚀 Disparar agora
                        </button>
                    </div>
                </form>

                <div style="margin-top:12px;font-size:12px;color:var(--muted);">
                    Dica: clique algumas vezes para enviar em lotes, sem travar navegador/servidor.
                </div>
            </div>
        </div>
    </div>

</div>

@endsection
