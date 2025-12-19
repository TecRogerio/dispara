@extends('layouts.app')

@section('content')

<div class="panel-header">
  <div>
    <h1 class="panel-title">Campanhas</h1>
    <p class="panel-subtitle">Gerencie suas campanhas, mensagens e disparos.</p>
  </div>

  <div style="display:flex;gap:10px;flex-wrap:wrap;">
    <a href="{{ route('campanhas.create') }}" class="z-btn z-btn-primary">
      + Nova campanha
    </a>
  </div>
</div>

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

{{-- Desktop/tablet: tabela --}}
<div class="d-none d-md-block">
  <div class="z-card">
    <div class="z-card-header">
      <strong>Lista de campanhas</strong>
      <div style="display:flex;gap:10px;align-items:center;">
        <span style="font-size:12px;color:var(--muted);">Total: {{ $campaigns->count() }}</span>
      </div>
    </div>

    <div class="z-card-body" style="padding:0;">
      <table class="z-table">
        <thead>
          <tr>
            <th>Campanha</th>
            <th>Status</th>
            <th>Instância</th>
            <th>Criada</th>
            <th style="text-align:right;width:160px;">Ações</th>
          </tr>
        </thead>

        <tbody>
        @forelse($campaigns as $c)
          @php
            $status = $c->status ?? 'draft';

            // Badge do tema (sem bootstrap)
            $badgeClass = 'z-badge';
            if ($status === 'running') $badgeClass .= ' z-badge-warn';
            elseif ($status === 'finished') $badgeClass .= ' z-badge-ok';
            elseif ($status === 'failed') $badgeClass .= ' z-badge-off';
            elseif ($status === 'paused') $badgeClass .= ' z-badge-warn';

            $instanceLabel = optional($c->instance)->label ?? optional($c->instance)->instance_name ?? '-';
          @endphp

          <tr>
            <td>
              <div style="display:flex;flex-direction:column;gap:2px;">
                <a href="{{ route('campanhas.show', $c->id) }}"
                   class="link"
                   style="font-weight:900; font-size:14px;">
                  {{ $c->name }}
                </a>
                <span style="font-size:12px;color:var(--muted);">ID #{{ $c->id }}</span>
              </div>
            </td>

            <td>
              <span class="{{ $badgeClass }}">{{ $status }}</span>
            </td>

            <td>
              <div style="display:flex;flex-direction:column;gap:2px;">
                <span style="font-weight:800;">{{ $instanceLabel }}</span>
                @if(optional($c->instance)->instance_name)
                  <span style="font-size:12px;color:var(--muted);">{{ $c->instance->instance_name }}</span>
                @endif
              </div>
            </td>

            <td>
              <div style="display:flex;flex-direction:column;gap:2px;">
                <span style="font-weight:800;">
                  {{ optional($c->created_at)->format('d/m/Y') ?? '-' }}
                </span>
                <span style="font-size:12px;color:var(--muted);">
                  {{ optional($c->created_at)->format('H:i') ?? '' }}
                </span>
              </div>
            </td>

            <td style="text-align:right;">
              <a href="{{ route('campanhas.show', $c->id) }}" class="z-btn">
                Abrir
              </a>
            </td>
          </tr>
        @empty
          <tr>
            <td colspan="5" style="padding:16px 12px;color:var(--muted);">
              Nenhuma campanha ainda.
            </td>
          </tr>
        @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- Mobile: cards --}}
<div class="d-md-none">
  <div style="display:grid;gap:12px;">
    @forelse($campaigns as $c)
      @php
        $status = $c->status ?? 'draft';
        $badgeClass = 'z-badge';
        if ($status === 'running') $badgeClass .= ' z-badge-warn';
        elseif ($status === 'finished') $badgeClass .= ' z-badge-ok';
        elseif ($status === 'failed') $badgeClass .= ' z-badge-off';
        elseif ($status === 'paused') $badgeClass .= ' z-badge-warn';

        $instanceLabel = optional($c->instance)->label ?? optional($c->instance)->instance_name ?? '-';
      @endphp

      <div class="z-card">
        <div class="z-card-body">
          <div style="display:flex;justify-content:space-between;gap:10px;align-items:flex-start;">
            <div style="min-width:0;">
              <div style="font-weight:900;font-size:16px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                {{ $c->name }}
              </div>
              <div style="font-size:12px;color:var(--muted);">ID #{{ $c->id }}</div>
            </div>
            <span class="{{ $badgeClass }}">{{ $status }}</span>
          </div>

          <div style="margin-top:10px;font-size:13px;color:var(--muted);line-height:1.5;">
            <div><strong style="color:rgba(229,231,235,.92);">Instância:</strong> {{ $instanceLabel }}</div>
            <div><strong style="color:rgba(229,231,235,.92);">Criada:</strong> {{ optional($c->created_at)->format('d/m/Y H:i') ?? '-' }}</div>
          </div>

          <div style="margin-top:12px;">
            <a href="{{ route('campanhas.show', $c->id) }}" class="z-btn" style="width:100%;justify-content:center;">
              Abrir campanha
            </a>
          </div>
        </div>
      </div>
    @empty
      <div style="color:var(--muted);">Nenhuma campanha ainda.</div>
    @endforelse
  </div>
</div>

@endsection
