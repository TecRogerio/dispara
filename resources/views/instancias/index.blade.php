@extends('layouts.app')

@section('content')

<div class="panel-header">
    <div>
        <h1 class="panel-title">Minhas Instâncias (WhatsApp)</h1>
        <p class="panel-subtitle">Gerencie as conexões da Evolution/WhatsApp e controle o envio diário.</p>
    </div>

    <a href="{{ route('instancias.create') }}" class="z-btn z-btn-primary">
        + Nova instância
    </a>
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

@if (session('info'))
    <div class="z-card" style="margin-bottom:14px;">
        <div class="z-card-body">
            <div class="z-badge z-badge-warn">ℹ️ {{ session('info') }}</div>
        </div>
    </div>
@endif

<div class="z-card">
    <div class="z-card-header">
        <strong>Lista de Instâncias</strong>
        <div style="display:flex;gap:10px;align-items:center;">
            <span style="font-size:12px;color:var(--muted);">Total: {{ $instances->count() }}</span>
        </div>
    </div>

    <div class="z-card-body" style="padding:0;">
        <table class="z-table">
            <thead>
                <tr>
                    <th>Label</th>
                    <th>Instance</th>
                    <th>Ativa</th>
                    <th>Limite/dia</th>
                    <th>Status (Evo)</th>
                    <th style="width: 380px;">Ações</th>
                </tr>
            </thead>

            <tbody>
            @forelse ($instances as $row)
                <tr data-instance-row
                    data-status-url="{{ route('instancias.status', $row->id) }}"
                    data-instance-id="{{ $row->id }}"
                >
                    <td>{{ $row->label ?? '-' }}</td>

                    <td>
                        <strong>{{ $row->instance_name }}</strong>
                        <div style="font-size:12px;color:var(--muted);margin-top:2px;">
                            ID: {{ $row->id }}
                        </div>
                    </td>

                    <td>
                        @if ((bool)$row->enabled)
                            <span class="z-badge z-badge-ok">SIM</span>
                        @else
                            <span class="z-badge z-badge-warn">NÃO</span>
                        @endif
                    </td>

                    <td>{{ (int)($row->daily_limit ?? 200) }}</td>

                    <td>
                        <span id="evoStatusBadge-{{ $row->id }}" class="z-badge z-badge-warn" style="font-size:12px;">
                            ⏳ Verificando...
                        </span>
                    </td>

                    <td>
                        <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">

                            {{-- Conectar --}}
                            <form method="POST"
                                  action="{{ route('instancias.connect', $row->id) }}"
                                  id="connectForm-{{ $row->id }}">
                                @csrf
                                <button type="submit" class="z-btn z-btn-primary" id="connectBtn-{{ $row->id }}">
                                    Conectar
                                </button>
                            </form>

                            {{-- Desconectar --}}
                            <form method="POST"
                                  action="{{ route('instancias.disconnect', $row->id) }}"
                                  id="disconnectForm-{{ $row->id }}"
                                  style="display:none;"
                                  onsubmit="return confirm('Desconectar esta instância da Evolution/WhatsApp?');">
                                @csrf
                                <button type="submit" class="z-btn z-btn-danger" id="disconnectBtn-{{ $row->id }}">
                                    Desconectar
                                </button>
                            </form>

                            {{-- Ativar/Desativar (enabled) --}}
                            <form method="POST" action="{{ route('instancias.toggle', $row->id) }}">
                                @csrf
                                <button type="submit" class="z-btn">
                                    {{ ((bool)$row->enabled) ? 'Desativar' : 'Ativar' }}
                                </button>
                            </form>

                            {{-- Remover --}}
                            <form method="POST"
                                  action="{{ route('instancias.destroy', $row->id) }}"
                                  onsubmit="return confirm('Remover esta instância do sistema?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="z-btn z-btn-danger">
                                    Remover
                                </button>
                            </form>

                        </div>

                        <div id="rowHint-{{ $row->id }}" style="display:none;margin-top:8px;font-size:12px;color:var(--muted);">
                            Aguarde...
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" style="padding:16px 12px;color:var(--muted);">
                        Nenhuma instância cadastrada ainda. Clique em <strong>+ Nova instância</strong>.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

@if (app()->environment('local'))
    <div style="margin-top:14px;font-size:12px;color:var(--muted);">
        Debug local:
        <a class="link" href="{{ url('/evolution/ping') }}">/evolution/ping</a>
        ·
        <a class="link" href="{{ url('/evolution/check') }}">/evolution/check</a>
    </div>
@endif

<script>
(function () {
    const rows = document.querySelectorAll('[data-instance-row]');
    if (!rows.length) return;

    let busy = false;

    function withTimeout(ms, promise) {
        const controller = new AbortController();
        const t = setTimeout(() => controller.abort(), ms);
        return Promise.race([
            promise(controller.signal).finally(() => clearTimeout(t)),
        ]);
    }

    function setBadge(badge, type, text) {
        if (!badge) return;
        badge.className = 'z-badge ' + (type === 'ok' ? 'z-badge-ok' : type === 'off' ? 'z-badge-off' : 'z-badge-warn');
        badge.textContent = text;
    }

    function normalizeState(data) {
        const raw = (data && data.state) ? String(data.state) : '';
        return raw ? raw.toLowerCase() : '';
    }

    function setRowLoading(id, isLoading, msg) {
        const hint = document.getElementById('rowHint-' + id);
        if (!hint) return;

        if (isLoading) {
            hint.style.display = 'block';
            hint.textContent = msg || 'Aguarde...';
        } else {
            hint.style.display = 'none';
            hint.textContent = '';
        }
    }

    function setButtonsLoading(id, isLoading) {
        const connectBtn = document.getElementById('connectBtn-' + id);
        const disconnectBtn = document.getElementById('disconnectBtn-' + id);

        if (connectBtn) {
            connectBtn.disabled = !!isLoading;
            connectBtn.style.opacity = isLoading ? '0.7' : '1';
            if (isLoading) connectBtn.textContent = 'Aguarde...';
            else connectBtn.textContent = 'Conectar';
        }

        if (disconnectBtn) {
            disconnectBtn.disabled = !!isLoading;
            disconnectBtn.style.opacity = isLoading ? '0.7' : '1';
            if (isLoading) disconnectBtn.textContent = 'Aguarde...';
            else disconnectBtn.textContent = 'Desconectar';
        }
    }

    async function updateRow(row) {
        const id = row.getAttribute('data-instance-id');
        const url = row.getAttribute('data-status-url');

        const badge = document.getElementById('evoStatusBadge-' + id);
        const connectForm = document.getElementById('connectForm-' + id);
        const disconnectForm = document.getElementById('disconnectForm-' + id);

        if (!id || !url) return;

        try {
            const data = await withTimeout(6000, (signal) =>
                fetch(url, { headers: { 'Accept': 'application/json' }, cache: 'no-store', signal })
                    .then(r => r.json())
            );

            const state = normalizeState(data);
            const connected = (data && data.connected === true) || state === 'open';

            if (connected) {
                setBadge(badge, 'ok', '✅ Conectado');
                if (connectForm) connectForm.style.display = 'none';
                if (disconnectForm) disconnectForm.style.display = 'inline-block';
            } else if (state) {
                // estados típicos: close/connecting/qr/etc
                setBadge(badge, 'warn', '⏳ ' + state);
                if (connectForm) connectForm.style.display = 'inline-block';
                if (disconnectForm) disconnectForm.style.display = 'none';
            } else {
                setBadge(badge, 'warn', '⚠️ Indisponível');
                if (connectForm) connectForm.style.display = 'inline-block';
                if (disconnectForm) disconnectForm.style.display = 'none';
            }

        } catch (e) {
            setBadge(badge, 'warn', '⚠️ Indisponível');
            if (connectForm) connectForm.style.display = 'inline-block';
            if (disconnectForm) disconnectForm.style.display = 'none';
        }
    }

    async function tick() {
        if (busy) return;
        busy = true;

        try {
            await Promise.all(Array.from(rows).map(row => updateRow(row)));
        } finally {
            busy = false;
        }
    }

    // UX: ao clicar desconectar, trava botões e força refresh do status logo em seguida
    rows.forEach(row => {
        const id = row.getAttribute('data-instance-id');
        const disconnectForm = document.getElementById('disconnectForm-' + id);
        const connectForm = document.getElementById('connectForm-' + id);

        if (disconnectForm) {
            disconnectForm.addEventListener('submit', () => {
                setRowLoading(id, true, 'Solicitando desconexão na Evolution...');
                setButtonsLoading(id, true);
                // quando voltar do POST, a página recarrega com flash message.
                // mas se o navegador não recarregar por algum motivo, a gente faz refresh do status depois.
                setTimeout(() => tick(), 2500);
                setTimeout(() => { setRowLoading(id, false); setButtonsLoading(id, false); }, 8000);
            });
        }

        if (connectForm) {
            connectForm.addEventListener('submit', () => {
                setRowLoading(id, true, 'Abrindo QR Code...');
                setButtonsLoading(id, true);
            });
        }
    });

    // primeira verificação rápida
    tick();

    // atualiza a cada 5s (sem empilhar requisições)
    setInterval(tick, 5000);
})();
</script>

@endsection
