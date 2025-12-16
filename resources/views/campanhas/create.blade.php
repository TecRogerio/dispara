@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 820px; margin-top: 30px;">
  <div style="background:#fff;border-radius:14px;padding:28px;box-shadow:0 6px 18px rgba(0,0,0,.06);">

    <div style="display:flex;justify-content:space-between;align-items:center;gap:15px;flex-wrap:wrap;">
      <h2 style="margin:0;font-weight:700;">Nova Campanha</h2>
      <a href="{{ route('campanhas.index') }}" style="font-weight:600;">← Voltar</a>
    </div>

    @if ($errors->any())
      <div style="margin-top:16px;padding:12px 14px;border-radius:10px;background:#ffecec;border:1px solid #ffbdbd;color:#7a1b1b;">
        <ul style="margin:0;padding-left:18px;">
          @foreach($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    @if (empty($instances) || count($instances) === 0)
      <div style="margin-top:16px;padding:12px 14px;border-radius:10px;background:#fff7e6;border:1px solid #ffd39a;color:#7a4a00;">
        Você ainda não tem nenhuma instância ativa. Vá em <strong>Instâncias</strong> e conecte/ative uma antes de criar campanha.
      </div>
    @endif

    <form method="POST" action="{{ route('campanhas.store') }}" style="margin-top:18px;display:grid;gap:14px;">
      @csrf

      <div>
        <label style="font-weight:600;">Nome da campanha</label>
        <input
          name="name"
          value="{{ old('name') }}"
          class="form-control"
          placeholder="Ex: Promo Dezembro"
          required
        >
      </div>

      <div>
        <label style="font-weight:600;">Instância de origem</label>
        <select name="whatsapp_instance_id" class="form-control" required @if(empty($instances) || count($instances)===0) disabled @endif>
          <option value="">Selecione...</option>
          @foreach($instances as $i)
            <option value="{{ $i->id }}" @selected(old('whatsapp_instance_id')==$i->id)>
              {{ $i->label ?? $i->instance_name }} ({{ $i->instance_name }})
            </option>
          @endforeach
        </select>
        <div style="font-size:12px;color:#666;margin-top:6px;">Somente instâncias ativas aparecem aqui.</div>
      </div>

      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <div>
          <label style="font-weight:600;">Delay mínimo (ms)</label>
          <input
            type="number"
            name="delay_min_ms"
            value="{{ old('delay_min_ms', 1500) }}"
            class="form-control"
            min="500"
            max="60000"
            step="100"
            required
          >
          <div style="font-size:12px;color:#666;margin-top:6px;">Recomendado: 1500ms+</div>
        </div>

        <div>
          <label style="font-weight:600;">Delay máximo (ms)</label>
          <input
            type="number"
            name="delay_max_ms"
            value="{{ old('delay_max_ms', 4000) }}"
            class="form-control"
            min="500"
            max="60000"
            step="100"
            required
          >
          <div style="font-size:12px;color:#666;margin-top:6px;">Deve ser maior ou igual ao mínimo</div>
        </div>
      </div>

      <button type="submit" class="btn btn-primary" style="margin-top:6px;" @if(empty($instances) || count($instances)===0) disabled @endif>
        Criar campanha
      </button>
    </form>

  </div>
</div>
@endsection
