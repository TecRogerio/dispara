@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 1100px; margin-top: 18px;">

    {{-- Header --}}
    <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-3">
        <div style="min-width: 260px;">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <h2 class="mb-0" style="font-weight: 900; letter-spacing: .2px;">
                    Campanha: {{ $campaign->name }}
                </h2>

                @php
                    $status = $campaign->status ?? 'draft';
                    $badge = 'bg-secondary';
                    if ($status === 'running') $badge = 'bg-primary';
                    elseif ($status === 'finished') $badge = 'bg-success';
                    elseif ($status === 'failed') $badge = 'bg-danger';
                    elseif ($status === 'paused') $badge = 'bg-warning text-dark';
                @endphp

                <span class="badge {{ $badge }}" style="font-weight: 900; padding: 6px 10px; border-radius: 999px;">
                    {{ $status }}
                </span>
            </div>

            <div class="text-muted mt-2" style="font-size: 13px; line-height: 1.5;">
                <div><strong>ID:</strong> {{ $campaign->id }}</div>
                <div>
                    <strong>Instância:</strong>
                    {{ optional($campaign->instance)->label ?? optional($campaign->instance)->instance_name ?? '-' }}
                    @if(optional($campaign->instance)->instance_name)
                        <span class="text-muted">({{ $campaign->instance->instance_name }})</span>
                    @endif
                </div>
                <div>
                    <strong>Delay:</strong>
                    {{ (int)($campaign->delay_min_seconds ?? 1) }}s → {{ (int)($campaign->delay_max_seconds ?? 1) }}s
                </div>
                <div>
                    <strong>Criada:</strong>
                    {{ optional($campaign->created_at)->format('d/m/Y H:i') ?? '-' }}
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('campanhas.index') }}" class="btn btn-outline-secondary fw-bold">
                ← Voltar
            </a>

            <a href="{{ route('campanhas.create') }}" class="btn btn-primary fw-bold">
                + Nova campanha
            </a>
        </div>
    </div>

    {{-- Feedback --}}
    @if ($errors->any())
        <div class="alert alert-danger">
            <div class="fw-bold mb-2">Atenção:</div>
            <ul class="mb-0">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    {{-- ✅ Resumo de destinatários --}}
    @php
        $recipients = $campaign->recipients ?? collect();
        $totalRecipients = $recipients->count();
        $validRecipients   = $recipients->where('is_valid', 1)->count();
        $invalidRecipients = $totalRecipients - $validRecipients;

        $pendingRecipients = $recipients->whereIn('status', ['pending'])->count();
        $sentRecipients    = $recipients->whereIn('status', ['sent'])->count();
        $failedRecipients  = $recipients->whereIn('status', ['failed'])->count();
    @endphp

    <div class="card mb-3" style="border-radius:16px; border:1px solid rgba(15,23,42,.10); box-shadow: 0 10px 24px rgba(15,23,42,.06);">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <div class="fw-bold" style="font-size: 14px;">Destinatários (resumo)</div>
                    <div class="text-muted mt-1" style="font-size: 12px;">
                        Para visualizar nomes e números carregados, use “Ver destinatários”.
                    </div>

                    <div class="mt-2 d-flex flex-wrap gap-2">
                        <span class="badge bg-light text-dark" style="border:1px solid rgba(15,23,42,.10); padding:8px 10px; border-radius:999px; font-weight:900;">
                            Total: {{ $totalRecipients }}
                        </span>
                        <span class="badge bg-light text-dark" style="border:1px solid rgba(15,23,42,.10); padding:8px 10px; border-radius:999px; font-weight:900;">
                            Válidos: {{ $validRecipients }}
                        </span>
                        <span class="badge bg-light text-dark" style="border:1px solid rgba(15,23,42,.10); padding:8px 10px; border-radius:999px; font-weight:900;">
                            Inválidos: {{ $invalidRecipients }}
                        </span>
                        <span class="badge bg-light text-dark" style="border:1px solid rgba(15,23,42,.10); padding:8px 10px; border-radius:999px; font-weight:900;">
                            Pendentes: {{ $pendingRecipients }}
                        </span>
                        <span class="badge bg-light text-dark" style="border:1px solid rgba(15,23,42,.10); padding:8px 10px; border-radius:999px; font-weight:900;">
                            Enviados: {{ $sentRecipients }}
                        </span>
                        <span class="badge bg-light text-dark" style="border:1px solid rgba(15,23,42,.10); padding:8px 10px; border-radius:999px; font-weight:900;">
                            Falharam: {{ $failedRecipients }}
                        </span>
                    </div>
                </div>

                <div class="d-flex gap-2 flex-wrap">
                    <a href="{{ route('campanhas.recipients', $campaign->id) }}" class="btn btn-outline-primary fw-bold">
                        👥 Ver destinatários
                    </a>

                    <form method="POST"
                          action="{{ route('campanhas.recipients.dedup', $campaign->id) }}"
                          onsubmit="return confirm('Remover contatos duplicados por número nesta campanha? (mantém apenas 1 por número)');"
                          class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary fw-bold">
                            🧹 Remover duplicados
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Layout em colunas (desktop) / empilhado (mobile) --}}
    <div class="row g-3">

        {{-- 1) Importar destinatários --}}
        <div class="col-12 col-lg-6">
            <div class="card h-100" style="border-radius:16px; border:1px solid rgba(15,23,42,.10); box-shadow: 0 10px 24px rgba(15,23,42,.06);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                        <div>
                            <div class="fw-bold">1) Importar / Colar destinatários</div>
                            <div class="text-muted" style="font-size:12px;">
                                Aceita TXT/CSV ou colar lista (um por linha)
                            </div>
                        </div>

                        <a href="{{ route('campanhas.recipients', $campaign->id) }}" class="btn btn-sm btn-outline-primary fw-bold">
                            Ver lista
                        </a>
                    </div>

                    <form method="POST"
                          action="{{ route('campanhas.recipients.import', $campaign->id) }}"
                          enctype="multipart/form-data"
                          class="mt-3">
                        @csrf

                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="ddi55" value="1" checked id="ddi55">
                            <label class="form-check-label fw-bold" for="ddi55">
                                Inserir DDI +55 automaticamente (quando vier só DDD+número)
                            </label>
                            <div class="text-muted" style="font-size:12px;">
                                Ex: “54912345678” vira “55 54 912345678”
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label fw-bold">Cole aqui (um por linha) ou “nome;telefone”</label>
                            <textarea name="content" rows="7" class="form-control" placeholder="Ex:
Maria; 54 91234-5678
João, 54991234567
54912345678">{{ old('content') }}</textarea>
                            <div class="text-muted mt-1" style="font-size:12px;">
                                Dica: “nome;telefone” ou “nome,telefone”.
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Ou envie arquivo (.txt / .csv)</label>
                            <input type="file" name="file" class="form-control" accept=".txt,.csv">
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            <button type="submit" class="btn btn-primary fw-bold">
                                Importar
                            </button>

                            <a href="{{ route('campanhas.show', $campaign->id) }}" class="btn btn-outline-secondary fw-bold">
                                Recarregar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- 2) Mensagens --}}
        <div class="col-12 col-lg-6">
            <div class="card h-100" style="border-radius:16px; border:1px solid rgba(15,23,42,.10); box-shadow: 0 10px 24px rgba(15,23,42,.06);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                        <div>
                            <div class="fw-bold">2) Mensagens da campanha</div>
                            <div class="text-muted" style="font-size:12px;">(por enquanto só texto)</div>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('campanhas.messages.store', $campaign->id) }}" class="mt-3">
                        @csrf
                        <label class="form-label fw-bold">Mensagem que será enviada no WhatsApp</label>
                        <textarea name="text" rows="4" class="form-control" placeholder="Digite a mensagem...">{{ old('text') }}</textarea>

                        <div class="mt-2 d-flex gap-2 flex-wrap">
                            <button type="submit" class="btn btn-outline-primary fw-bold">
                                + Adicionar mensagem
                            </button>
                        </div>
                    </form>

                    <hr class="my-3">

                    <div>
                        @if(isset($campaign->messages) && $campaign->messages->count() > 0)
                            <div class="fw-bold mb-2">Mensagens cadastradas</div>

                            <div class="d-grid gap-2">
                                @foreach($campaign->messages as $m)
                                    <div class="p-3 rounded" style="border:1px solid rgba(15,23,42,.10); background: rgba(15,23,42,.02);">
                                        <div class="d-flex justify-content-between align-items-start gap-2 flex-wrap">
                                            <div style="min-width: 220px;">
                                                <div class="fw-bold">#{{ (int)($m->position ?? 0) }}</div>
                                                <div style="white-space:pre-wrap; color:#0f172a;">{{ $m->text }}</div>
                                            </div>

                                            <form method="POST"
                                                  action="{{ route('campanhas.messages.destroy', [$campaign->id, $m->id]) }}"
                                                  onsubmit="return confirm('Remover esta mensagem?');"
                                                  class="m-0">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm fw-bold">
                                                    Remover
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-muted">Nenhuma mensagem cadastrada ainda.</div>
                        @endif
                    </div>

                </div>
            </div>
        </div>

        {{-- 3) Disparo --}}
        <div class="col-12">
            <div class="card" style="border-radius:16px; border:1px solid rgba(22,163,74,.25); box-shadow: 0 10px 24px rgba(15,23,42,.06);">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                        <div>
                            <div class="fw-bold">3) Disparar mensagens</div>
                            <div class="text-muted" style="font-size:12px;">
                                Executa em lote por clique (recomendado)
                            </div>
                        </div>
                        <span class="badge bg-success" style="border-radius:999px; padding:6px 10px; font-weight:900;">
                            ✅ Lote por clique
                        </span>
                    </div>

                    <form method="POST" action="{{ route('campanhas.dispatch', $campaign->id) }}" class="mt-3 row g-2 align-items-end">
                        @csrf

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-bold">Limite por clique</label>
                            <input type="number" name="limit" class="form-control" value="20" min="1" max="50">
                            <div class="text-muted mt-1" style="font-size:12px;">Máximo: 50</div>
                        </div>

                        <div class="col-12 col-md-4">
                            <button type="submit" class="btn btn-success fw-bold w-100">
                                🚀 Disparar agora
                            </button>
                        </div>
                    </form>

                    <div class="text-muted mt-3" style="font-size:12px;">
                        Dica: clique algumas vezes para enviar em lotes, sem travar navegador/servidor.
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
