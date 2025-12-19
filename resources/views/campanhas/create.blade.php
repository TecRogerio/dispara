@extends('layouts.app')

@section('content')

<div class="panel-header">
  <div>
    <h1 class="panel-title">Nova Campanha</h1>
    <p class="panel-subtitle">Configure a instância, limites e delays para um envio mais seguro.</p>
  </div>

  <a href="{{ route('campanhas.index') }}" class="z-btn">
    ← Voltar
  </a>
</div>

@if ($errors->any())
  <div class="z-card" style="margin-bottom:14px;">
    <div class="z-card-body">
      <div class="z-badge z-badge-off" style="margin-bottom:10px;">⛔ Verifique os campos abaixo</div>
      <ul style="margin:0;padding-left:18px;color:#fecaca;font-size:13px;line-height:1.5;">
        @foreach($errors->all() as $e)
          <li>{{ $e }}</li>
        @endforeach
      </ul>
    </div>
  </div>
@endif

@if (empty($instances) || count($instances) === 0)
  <div class="z-card" style="margin-bottom:14px;">
    <div class="z-card-body">
      <div class="z-badge z-badge-warn">
        ℹ️ Você ainda não tem nenhuma instância ativa. Vá em <strong>Instâncias</strong> e conecte/ative uma antes de criar campanha.
      </div>
    </div>
  </div>
@endif

<div class="z-card">
  <div class="z-card-header">
    <strong>Dados da campanha</strong>
    <span style="font-size:12px;color:var(--muted);">Preencha os campos obrigatórios</span>
  </div>

  <div class="z-card-body">
    <form method="POST" action="{{ route('campanhas.store') }}" style="display:grid;gap:14px;">
      @csrf

      <div class="field" style="margin-bottom:0;">
        <label for="name">Nome da campanha</label>
        <input
          id="name"
          name="name"
          value="{{ old('name') }}"
          class="z-input"
          placeholder="Ex: Promo Dezembro"
          required
        >
      </div>

      <div class="field" style="margin-bottom:0;">
        <label for="whatsapp_instance_id">Instância de origem</label>
        <select
          id="whatsapp_instance_id"
          name="whatsapp_instance_id"
          class="z-input"
          required
          @if(empty($instances) || count($instances)===0) disabled @endif
        >
          <option value="">Selecione...</option>
          @foreach($instances as $i)
            <option value="{{ $i->id }}" @selected(old('whatsapp_instance_id')==$i->id)>
              {{ $i->label ?? $i->instance_name }} ({{ $i->instance_name }})
            </option>
          @endforeach
        </select>

        <div style="font-size:12px;color:var(--muted);margin-top:6px;">
          Somente instâncias ativas aparecem aqui.
        </div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div class="field" style="margin-bottom:0;">
          <label for="delay_min_ms">Delay mínimo (ms)</label>
          <input
            id="delay_min_ms"
            type="number"
            name="delay_min_ms"
            value="{{ old('delay_min_ms', 1500) }}"
            class="z-input"
            min="500"
            max="60000"
            step="100"
            required
          >
          <div style="font-size:12px;color:var(--muted);margin-top:6px;">
            Recomendado: 1500ms+
          </div>
        </div>

        <div class="field" style="margin-bottom:0;">
          <label for="delay_max_ms">Delay máximo (ms)</label>
          <input
            id="delay_max_ms"
            type="number"
            name="delay_max_ms"
            value="{{ old('delay_max_ms', 4000) }}"
            class="z-input"
            min="500"
            max="60000"
            step="100"
            required
          >
          <div style="font-size:12px;color:var(--muted);margin-top:6px;">
            Deve ser maior ou igual ao mínimo
          </div>
        </div>
      </div>

      <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-top:6px;">
        <button
          type="submit"
          class="z-btn z-btn-primary"
          @if(empty($instances) || count($instances)===0) disabled @endif
          style="@if(empty($instances) || count($instances)===0) opacity:.55; cursor:not-allowed; @endif"
        >
          Criar campanha
        </button>

        <a href="{{ route('campanhas.index') }}" class="z-btn">
          Cancelar
        </a>
      </div>
    </form>
  </div>
</div>

@endsection
