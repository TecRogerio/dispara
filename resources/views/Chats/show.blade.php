@extends('layouts.app')

@section('content')
@php
  // $chat, $messages, $messagesAsc, $chats vem do controller
@endphp

<style>
  .chat-shell{ display:grid; grid-template-columns: 360px 1fr; gap:14px; }
  .chat-list{ height: calc(100vh - 210px); overflow:auto; }
  .chat-thread{ height: calc(100vh - 210px); overflow:auto; padding:16px; }
  .chat-item{
    display:block; padding:12px 12px; border-radius:14px; text-decoration:none;
    border:1px solid rgba(15,23,42,.08); background: rgba(15,23,42,.02);
    transition:.12s;
  }
  .chat-item:hover{ transform: translateY(-1px); border-color: rgba(37,99,235,.20); background: rgba(37,99,235,.06); }
  .chat-item.active{ border-color: rgba(37,99,235,.30); background: rgba(37,99,235,.10); }
  .bubble{
    max-width: 680px; padding:10px 12px; border-radius:16px; border:1px solid rgba(15,23,42,.10);
    box-shadow: 0 8px 20px rgba(15,23,42,.06);
    white-space: pre-wrap;
  }
  .bubble.in{ background:#fff; }
  .bubble.out{ background: rgba(37,99,235,.10); border-color: rgba(37,99,235,.22); }
  .msg-row{ display:flex; gap:10px; margin-bottom:10px; }
  .msg-row.in{ justify-content:flex-start; }
  .msg-row.out{ justify-content:flex-end; }
  .msg-meta{ font-size:11px; color: var(--muted); margin-top:4px; font-weight:700; }
  .composer{ display:flex; gap:10px; }
  .composer textarea{ resize:none; min-height: 44px; max-height: 120px; }
  @media (max-width: 980px){
    .chat-shell{ grid-template-columns: 1fr; }
    .chat-list{ height:auto; max-height: 280px; }
    .chat-thread{ height:auto; max-height: 420px; }
  }
</style>

<div class="panel-header">
  <div>
    <h1 class="panel-title">Conversa</h1>
    <p class="panel-subtitle">
      <strong>{{ $chat->title ?: $chat->remote_jid }}</strong>
      · {{ $chat->remote_jid }}
    </p>
  </div>

  <a href="{{ route('chats.index') }}" class="z-btn">← Voltar</a>
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

@if ($errors->any())
  <div class="z-card" style="margin-bottom:14px;">
    <div class="z-card-body">
      <div class="z-badge z-badge-off">⛔ {{ $errors->first() }}</div>
    </div>
  </div>
@endif

<div class="chat-shell">

  {{-- Sidebar: lista de chats --}}
  <div class="z-card">
    <div class="z-card-header">
      <strong>Conversas</strong>
      <span style="font-size:12px;color:var(--muted);">últimas 50</span>
    </div>

    <div class="z-card-body chat-list" style="display:grid; gap:10px;">
      @forelse($chats as $c)
        @php
          $active = ((int)$c->id === (int)$chat->id);
          $title = $c->title ?: $c->remote_jid;
          $lastAt = $c->last_message_at ? \Carbon\Carbon::parse($c->last_message_at)->format('d/m H:i') : '-';
        @endphp

        <a href="{{ route('chats.show', $c->id) }}" class="chat-item {{ $active ? 'active' : '' }}">
          <div style="display:flex;justify-content:space-between;gap:8px;align-items:flex-start;">
            <div style="min-width:0;">
              <div style="font-weight:900; color: var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                {{ $title }}
              </div>
              <div style="font-size:12px;color:var(--muted); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                {{ $c->remote_jid }}
              </div>
            </div>
            <div style="font-size:11px;color:var(--muted);font-weight:800;">
              {{ $lastAt }}
            </div>
          </div>
        </a>
      @empty
        <div style="color:var(--muted);">Nenhuma conversa.</div>
      @endforelse
    </div>
  </div>

  {{-- Thread --}}
  <div class="z-card">
    <div class="z-card-header">
      <strong>Mensagens</strong>
      <span style="font-size:12px;color:var(--muted);">
        Chat #{{ $chat->id }}
      </span>
    </div>

    <div class="chat-thread" id="chatThread">
      @forelse($messagesAsc as $m)
        @php
          $isOut = ($m->direction === 'outbound');
          $when = $m->message_at ? \Carbon\Carbon::parse($m->message_at)->format('d/m/Y H:i') : '';
          $status = $m->status ?? '';
        @endphp

        <div class="msg-row {{ $isOut ? 'out' : 'in' }}">
          <div>
            <div class="bubble {{ $isOut ? 'out' : 'in' }}">
              {{ $m->body }}
            </div>
            <div class="msg-meta" style="{{ $isOut ? 'text-align:right;' : '' }}">
              {{ $when }} @if($isOut && $status) · {{ $status }} @endif
            </div>
          </div>
        </div>
      @empty
        <div style="color:var(--muted); padding:14px;">
          Nenhuma mensagem ainda.
        </div>
      @endforelse
    </div>

    <div class="z-card-body" style="border-top:1px solid rgba(15,23,42,.08);">
      <form method="POST" action="{{ route('chats.send', $chat->id) }}">
        @csrf
        <div class="composer">
          <textarea
            class="z-input"
            name="text"
            placeholder="Digite uma mensagem..."
            rows="2"
            style="flex:1;"
            required
          >{{ old('text') }}</textarea>

          <button type="submit" class="z-btn z-btn-primary" style="min-width:140px;">
            Enviar
          </button>
        </div>
        <div style="margin-top:8px; font-size:12px; color: var(--muted);">
          Envio via Evolution (texto). Em seguida faremos “tempo real” com WebSocket.
        </div>
      </form>

      @if (method_exists($messages, 'links'))
        <div style="margin-top:12px;">
          {{ $messages->links() }}
        </div>
      @endif
    </div>
  </div>

</div>

<script>
  // desce pro final automaticamente ao abrir
  (function(){
    const el = document.getElementById('chatThread');
    if(el) el.scrollTop = el.scrollHeight;
  })();
</script>

@endsection
