@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 1100px; margin-top: 18px;">

  {{-- Header --}}
  <div class="d-flex align-items-start justify-content-between flex-wrap gap-2 mb-3">
    <div>
      <h2 class="mb-1" style="font-weight:900; letter-spacing:.2px;">Campanhas</h2>
      <div class="text-muted" style="font-size:13px;">
        Gerencie suas campanhas, mensagens e disparos.
      </div>
    </div>

    <div class="d-flex gap-2 flex-wrap">
      <a href="{{ route('campanhas.create') }}" class="btn btn-primary fw-bold">
        + Nova campanha
      </a>
    </div>
  </div>

  {{-- Alerts --}}
  @if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
  @endif

  @if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
  @endif

  {{-- Desktop/tablet: tabela --}}
  <div class="d-none d-md-block">
    <div class="card" style="border-radius:16px; border:1px solid rgba(15,23,42,.10); box-shadow: 0 10px 24px rgba(15,23,42,.08);">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th style="padding:14px 14px;">Campanha</th>
              <th style="padding:14px 14px;">Status</th>
              <th style="padding:14px 14px;">Instância</th>
              <th style="padding:14px 14px;">Criada</th>
              <th style="padding:14px 14px; text-align:right;">Ações</th>
            </tr>
          </thead>

          <tbody>
          @forelse($campaigns as $c)
            @php
              $status = $c->status ?? 'draft';

              // Badge “bootstrap-friendly”
              $badgeClass = 'bg-secondary';
              if ($status === 'running') $badgeClass = 'bg-primary';
              elseif ($status === 'finished') $badgeClass = 'bg-success';
              elseif ($status === 'failed') $badgeClass = 'bg-danger';
              elseif ($status === 'paused') $badgeClass = 'bg-warning text-dark';

              $instanceLabel = optional($c->instance)->label ?? optional($c->instance)->instance_name ?? '-';
            @endphp

            <tr>
              <td style="padding:14px 14px;">
                <div class="d-flex flex-column">
                  <a href="{{ route('campanhas.show', $c->id) }}"
                     style="font-weight:900; text-decoration:none; color:#0f172a;">
                    {{ $c->name }}
                  </a>
                  <small class="text-muted">ID #{{ $c->id }}</small>
                </div>
              </td>

              <td style="padding:14px 14px;">
                <span class="badge {{ $badgeClass }}" style="font-weight:900; padding:6px 10px; border-radius:999px;">
                  {{ $status }}
                </span>
              </td>

              <td style="padding:14px 14px;">
                <span style="font-weight:800;">{{ $instanceLabel }}</span>
                @if(optional($c->instance)->instance_name)
                  <div class="text-muted" style="font-size:12px;">{{ $c->instance->instance_name }}</div>
                @endif
              </td>

              <td style="padding:14px 14px;">
                <span style="font-weight:800;">
                  {{ optional($c->created_at)->format('d/m/Y') ?? '-' }}
                </span>
                <div class="text-muted" style="font-size:12px;">
                  {{ optional($c->created_at)->format('H:i') ?? '' }}
                </div>
              </td>

              <td style="padding:14px 14px; text-align:right;">
                <a href="{{ route('campanhas.show', $c->id) }}" class="btn btn-outline-primary btn-sm fw-bold">
                  Abrir
                </a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="text-muted" style="padding:18px 14px;">
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
    <div class="d-grid gap-2">
      @forelse($campaigns as $c)
        @php
          $status = $c->status ?? 'draft';
          $badgeClass = 'bg-secondary';
          if ($status === 'running') $badgeClass = 'bg-primary';
          elseif ($status === 'finished') $badgeClass = 'bg-success';
          elseif ($status === 'failed') $badgeClass = 'bg-danger';
          elseif ($status === 'paused') $badgeClass = 'bg-warning text-dark';

          $instanceLabel = optional($c->instance)->label ?? optional($c->instance)->instance_name ?? '-';
        @endphp

        <div class="card" style="border-radius:16px; border:1px solid rgba(15,23,42,.10); box-shadow: 0 10px 24px rgba(15,23,42,.06);">
          <div class="card-body">
            <div class="d-flex justify-content-between align-items-start gap-2">
              <div style="min-width:0;">
                <div style="font-weight:900; font-size:16px; color:#0f172a; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                  {{ $c->name }}
                </div>
                <div class="text-muted" style="font-size:12px;">ID #{{ $c->id }}</div>
              </div>
              <span class="badge {{ $badgeClass }}" style="font-weight:900; padding:6px 10px; border-radius:999px;">
                {{ $status }}
              </span>
            </div>

            <div class="mt-2 text-muted" style="font-size:13px;">
              <div><strong>Instância:</strong> {{ $instanceLabel }}</div>
              <div><strong>Criada:</strong> {{ optional($c->created_at)->format('d/m/Y H:i') ?? '-' }}</div>
            </div>

            <div class="mt-3 d-grid">
              <a href="{{ route('campanhas.show', $c->id) }}" class="btn btn-outline-primary fw-bold">
                Abrir campanha
              </a>
            </div>
          </div>
        </div>
      @empty
        <div class="text-muted">Nenhuma campanha ainda.</div>
      @endforelse
    </div>
  </div>

</div>
@endsection
