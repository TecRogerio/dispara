@extends('layouts.app')

@section('content')
<div class="container">
    <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
        <div>
            <h4 class="mb-1">Destinatários da Campanha: {{ $campaign->name ?? ('#'.$campaign->id) }}</h4>
            <small class="text-muted">
                Total: {{ $total }}
                @if(isset($valid) || isset($invalid))
                    • Válidos: {{ $valid ?? 0 }} • Inválidos: {{ $invalid ?? 0 }}
                @endif
            </small>
        </div>

        <div class="d-flex gap-2 flex-wrap">
            <a class="btn btn-outline-primary" href="{{ route('campanhas.show', $campaign->id) }}">
                ← Voltar para campanha
            </a>

            <form method="POST" action="{{ route('campaigns.recipients.dedup', $campaign->id) }}" class="m-0">
                @csrf
                <button type="submit" class="btn btn-outline-secondary"
                        onclick="return confirm('Remover contatos duplicados por número nesta campanha? (mantém apenas 1 por número)');">
                    Remover duplicados
                </button>
            </form>

            <a class="btn btn-primary" href="{{ route('campanhas.index') }}">
                Voltar para campanhas
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form class="row g-2 mb-3" method="GET" action="{{ route('campaigns.recipients.index', $campaign->id) }}">
        <div class="col-md-10">
            <input type="text" name="q" value="{{ $q ?? '' }}" class="form-control"
                   placeholder="Buscar por nome, número (digits) ou bruto (raw)...">
        </div>
        <div class="col-md-2 d-grid">
            <button class="btn btn-outline-primary">Buscar</button>
        </div>
    </form>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-striped mb-0 align-middle">
                <thead>
                    <tr>
                        <th style="width: 80px;">ID</th>
                        <th>Nome</th>
                        <th>Número (bruto)</th>
                        <th>Número (digits)</th>
                        <th style="width: 120px;">Válido</th>
                        <th style="width: 130px;">Status</th>
                        <th>Erro</th>
                        <th style="width: 160px;">Ações</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($recipients as $r)
                    @php
                        $isValid = (int)($r->is_valid ?? 0) === 1;
                        $status = (string)($r->status ?? 'pending');

                        // ✅ Compatível com PHP 7.4+ (sem match)
                        if ($status === 'sent') {
                            $statusBadge = 'bg-success';
                        } elseif ($status === 'failed') {
                            $statusBadge = 'bg-danger';
                        } elseif ($status === 'pending') {
                            $statusBadge = 'bg-secondary';
                        } else {
                            $statusBadge = 'bg-secondary';
                        }

                        // Label mais amigável
                        if ($status === 'sent') {
                            $statusLabel = 'sent';
                        } elseif ($status === 'failed') {
                            $statusLabel = 'failed';
                        } elseif ($status === 'pending') {
                            $statusLabel = 'pending';
                        } else {
                            $statusLabel = $status;
                        }
                    @endphp

                    <tr>
                        <td>{{ $r->id }}</td>
                        <td>{{ $r->name ?? '-' }}</td>

                        {{-- phone_raw é o que o usuário colou/importou --}}
                        <td>
                            <span class="text-muted">{{ $r->phone_raw ?? '-' }}</span>
                        </td>

                        {{-- phone_digits é o normalizado (55+DDD+numero) --}}
                        <td style="font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;">
                            {{ $r->phone_digits ?? '-' }}
                        </td>

                        <td>
                            @if($isValid)
                                <span class="badge bg-success">SIM</span>
                            @else
                                <span class="badge bg-warning text-dark">NÃO</span>
                            @endif
                        </td>

                        <td>
                            <span class="badge {{ $statusBadge }}">{{ $statusLabel }}</span>
                        </td>

                        <td class="text-muted" style="max-width: 320px;">
                            {{ $r->validation_error ?? '-' }}
                        </td>

                        <td>
                            <form method="POST"
                                  action="{{ route('campaigns.recipients.destroy', [$campaign->id, $r->id]) }}"
                                  onsubmit="return confirm('Excluir este contato da campanha?');"
                                  class="m-0">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-danger">Excluir</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center p-4 text-muted">
                            Nenhum destinatário carregado nesta campanha ainda.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-body">
            {{ $recipients->links() }}
        </div>
    </div>

    <div class="mt-3 text-muted" style="font-size: 12px;">
        Dica: “Número (digits)” é o formato usado no disparo (ex.: 55 + DDD + número). O “bruto” é o que veio do arquivo/cola.
    </div>
</div>
@endsection
