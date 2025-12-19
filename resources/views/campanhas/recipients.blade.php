@extends('layouts.app')

@section('content')

@php
    $campaign = $campaign ?? null;
@endphp

<div class="panel-header">
    <div>
        <h1 class="panel-title">Destinatários da Campanha</h1>
        <p class="panel-subtitle">
            Campanha: <strong>{{ $campaign->name ?? '-' }}</strong>
            · ID #{{ $campaign->id ?? '-' }}
        </p>
    </div>

    <a href="{{ route('campanhas.show', $campaign->id) }}" class="z-btn">
        ← Voltar para campanha
    </a>
</div>

{{-- Alerts --}}
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

{{-- Filtros (bate com o controller: q, status, valid) --}}
<div class="z-card" style="margin-bottom:14px;">
    <div class="z-card-header">
        <strong>Filtros</strong>
        <span style="font-size:12px;color:var(--muted);">Busca por nome/telefone</span>
    </div>
    <div class="z-card-body">
        <form method="GET" action="{{ route('campanhas.recipients', $campaign->id) }}" style="display:flex;gap:10px;flex-wrap:wrap;align-items:end;">
            <div style="flex:1;min-width:220px;">
                <label style="font-size:12px;color:var(--muted);font-weight:800;margin-bottom:6px;display:block;">Buscar</label>
                <input class="z-input" name="q" value="{{ $q ?? request('q') }}" placeholder="Nome, número, DDD...">
            </div>

            <div style="min-width:180px;">
                <label style="font-size:12px;color:var(--muted);font-weight:800;margin-bottom:6px;display:block;">Status</label>
                <select class="z-input" name="status">
                    @php $st = $status ?? request('status',''); @endphp
                    <option value="" @selected($st==='')>Todos</option>
                    <option value="pending" @selected($st==='pending')>pending</option>
                    <option value="sent" @selected($st==='sent')>sent</option>
                    <option value="failed" @selected($st==='failed')>failed</option>
                </select>
            </div>

            <div style="min-width:180px;">
                <label style="font-size:12px;color:var(--muted);font-weight:800;margin-bottom:6px;display:block;">Válido</label>
                @php $vf = $validFilter ?? request('valid',''); @endphp
                <select class="z-input" name="valid">
                    <option value="" @selected($vf==='')>Todos</option>
                    <option value="1" @selected((string)$vf==='1')>Somente válidos</option>
                    <option value="0" @selected((string)$vf==='0')>Somente inválidos</option>
                </select>
            </div>

            <div style="display:flex;gap:10px;">
                <button class="z-btn z-btn-primary" type="submit">Aplicar</button>
                <a class="z-btn" href="{{ route('campanhas.recipients', $campaign->id) }}">Limpar</a>
            </div>
        </form>
    </div>
</div>

<div class="z-card">
    <div class="z-card-header">
        <strong>Lista de destinatários</strong>
        <span style="font-size:12px;color:var(--muted);">
            Total: {{ method_exists($recipients, 'total') ? $recipients->total() : $recipients->count() }}
        </span>
    </div>

    <div class="z-card-body" style="padding:0;">
        <table class="z-table">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Telefone</th>
                    <th>Válido</th>
                    <th>Status</th>
                    <th style="width:140px;">Ações</th>
                </tr>
            </thead>

            <tbody>
            @forelse ($recipients as $r)
                @php
                    $validBadge  = (int)($r->is_valid ?? 0) === 1
                        ? 'z-badge z-badge-ok'
                        : 'z-badge z-badge-off';

                    $st2 = (string)($r->status ?? '');
                    $statusBadge = 'z-badge';
                    if ($st2 === 'sent') $statusBadge .= ' z-badge-ok';
                    elseif ($st2 === 'failed') $statusBadge .= ' z-badge-off';
                    elseif ($st2 === 'pending') $statusBadge .= ' z-badge-warn';

                    // Telefone: tenta phone_raw -> phone_digits -> phone_e164 -> phone
                    $phoneView = $r->phone_raw
                        ?? $r->phone_digits
                        ?? $r->phone_e164
                        ?? $r->phone
                        ?? null;
                @endphp

                <tr>
                    <td>{{ $r->name ?? '-' }}</td>

                    <td><strong>{{ $phoneView ?? '-' }}</strong></td>

                    <td>
                        <span class="{{ $validBadge }}">
                            {{ (int)($r->is_valid ?? 0) === 1 ? 'SIM' : 'NÃO' }}
                        </span>
                    </td>

                    <td>
                        <span class="{{ $statusBadge }}">
                            {{ $st2 !== '' ? $st2 : '-' }}
                        </span>
                    </td>

                    <td>
                        <form method="POST"
                              action="{{ route('campanhas.recipients.destroy', [$campaign->id, $r->id]) }}"
                              onsubmit="return confirm('Remover este destinatário?');"
                              style="margin:0;">
                            @csrf
                            @method('DELETE')

                            <button type="submit" class="z-btn z-btn-danger">
                                Remover
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="padding:16px;color:var(--muted);">
                        Nenhum destinatário encontrado para esta campanha.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Paginação --}}
@if (method_exists($recipients, 'links'))
    <div style="margin-top:14px;">
        {{ $recipients->links() }}
    </div>
@endif

@endsection
