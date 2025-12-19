@extends('layouts.app')

@section('content')
@php
  // $chats, $q, $lastByChat vem do controller
@endphp

<div class="panel-header">
  <div>
    <h1 class="panel-title">Conversas</h1>
    <p class="panel-subtitle">Inbox do WhatsApp — clique em uma conversa para abrir.</p>
  </div>

  <form method="GET" action="{{ route('chats.index') }}" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
    <input
      name="q"
      value="{{ $q ?? '' }}"
      placeholder="Buscar por número / título…"
      class="z-input"
      style="width:280px; max-width: 100%;"
    />
    <button class="z-btn" type="submit">Buscar</button>
  </form>
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

<div class="z-card">
  <div class="z-card-header">
    <strong>Lista de conversas</strong>
    <span style="font-size:12px;color:var(--muted);">
      Total: {{ method_exists($chats,'total') ? $chats->total() : $chats->count() }}
    </span>
  </div>

  <div class="z-card-body" style="padding:0;">
    <table class="z-table">
      <thead>
        <tr>
          <th>Contato</th>
          <th>Última mensagem</th>
          <th style="width:160px;">Data</th>
          <th style="width:140px;">Ações</th>
        </tr>
      </thead>
      <tbody>
      @forelse($chats as $c)
        @php
          $last = $lastByChat[$c->id] ?? null;

          $title = $c->title ?: $c->remote_jid;
          $subtitle = $c->remote_jid;

          $lastText = $last?->body ?? '-';
          $lastAt = $c->last_message_at ? \Carbon\Carbon::parse($c->last_message_at)->format('d/m/Y H:i') : '-';
          $dir = $last?->direction ?? null;
        @endphp

        <tr>
          <td>
            <div style="font-weight:900; color: var(--text);">{{ $title }}</div>
            <div style="font-size:12px; color: var(--muted);">{{ $subtitle }}</div>
          </td>

          <td>
            <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
              @if($dir === 'inbound')
                <span class="z-badge z-badge-warn">⬅ recebida</span>
              @elseif($dir === 'outbound')
                <span class="z-badge z-badge-ok">➡ enviada</span>
              @endif

              <span style="color:var(--text);">
                {{ \Illuminate\Support\Str::limit((string)$lastText, 90) }}
              </span>
            </div>
          </td>

          <td style="color:var(--muted); font-weight:700;">
            {{ $lastAt }}
          </td>

          <td>
            <a class="z-btn" href="{{ route('chats.show', $c->id) }}">Abrir</a>
          </td>
        </tr>
      @empty
        <tr>
          <td colspan="4" style="padding:16px;color:var(--muted);">
            Nenhuma conversa encontrada.
          </td>
        </tr>
      @endforelse
      </tbody>
    </table>
  </div>
</div>

@if (method_exists($chats, 'links'))
  <div style="margin-top:14px;">
    {{ $chats->links() }}
  </div>
@endif
@endsection
