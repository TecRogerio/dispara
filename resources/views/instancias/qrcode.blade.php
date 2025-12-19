@extends('layouts.app')

@section('content')

<div class="panel-header">
    <div>
        <h1 class="panel-title">Conectar WhatsApp</h1>
        <p class="panel-subtitle">Escaneie o QR Code abaixo para conectar a instância.</p>
    </div>

    <a href="{{ route('instancias.index') }}" class="z-btn">
        ← Voltar
    </a>
</div>

@if (!empty($error))
    <div class="z-card" style="margin-bottom:14px;">
        <div class="z-card-body">
            <div class="z-badge z-badge-off">⛔ {{ $error }}</div>
        </div>
    </div>
@endif

<div class="z-card" style="margin-bottom:14px;">
    <div class="z-card-body">
        <div style="display:flex;gap:14px;flex-wrap:wrap;align-items:flex-start;justify-content:space-between;">

            <div style="min-width:320px;flex:1;">
                <h3 style="margin:0 0 10px 0;">Instruções</h3>
                <ol style="margin:0;padding-left:18px;color:var(--muted);line-height:1.6;">
                    <li>Abra o WhatsApp no celular</li>
                    <li>Vá em <strong>Aparelhos conectados</strong></li>
                    <li>Toque em <strong>Conectar um aparelho</strong></li>
                    <li>Aponte a câmera para o QR ao lado</li>
                </ol>

                <div class="z-card" style="margin-top:14px;">
                    <div class="z-card-body">
                        <div class="z-badge z-badge-warn" id="statusBadge">
                            ⏳ Aguardando leitura do QR...
                        </div>

                        <div style="margin-top:10px;color:var(--muted);font-size:13px;" id="statusHint">
                            Assim que o WhatsApp conectar, você poderá voltar para a lista de instâncias.
                        </div>

                        <div style="margin-top:14px;display:flex;gap:10px;flex-wrap:wrap;">
                            <a class="z-btn" href="{{ route('instancias.index') }}" id="btnBackList">
                                Voltar para Instâncias
                            </a>
                        </div>
                    </div>
                </div>

                {{-- ✅ Comportamento (Settings) dentro do CRM --}}
                <div class="z-card" style="margin-top:14px;">
                    <div class="z-card-header">
                        <strong>Comportamento (Configurações da instância)</strong>
                    </div>
                    <div class="z-card-body">
                        <div id="settingsMsg" class="z-badge z-badge-warn" style="display:none;margin-bottom:12px;"></div>

                        <div style="display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;">
                            <label style="display:flex;gap:10px;align-items:center;">
                                <input type="checkbox" id="alwaysOnline">
                                <span>Sempre online</span>
                            </label>

                            <label style="display:flex;gap:10px;align-items:center;">
                                <input type="checkbox" id="groupsIgnore">
                                <span>Ignorar grupos</span>
                            </label>

                            <label style="display:flex;gap:10px;align-items:center;">
                                <input type="checkbox" id="rejectCall">
                                <span>Rejeitar chamadas</span>
                            </label>

                            <label style="display:flex;gap:10px;align-items:center;">
                                <input type="checkbox" id="readMessages">
                                <span>Marcar mensagens como lidas</span>
                            </label>

                            <label style="display:flex;gap:10px;align-items:center;">
                                <input type="checkbox" id="readStatus">
                                <span>Marcar status como vistos</span>
                            </label>

                            <label style="display:flex;gap:10px;align-items:center;">
                                <input type="checkbox" id="syncFullHistory">
                                <span>Sincronizar histórico completo</span>
                            </label>
                        </div>

                        <div style="margin-top:12px;">
                            <label style="display:block;font-size:13px;color:var(--muted);margin-bottom:6px;">
                                Mensagem ao rejeitar chamadas (opcional)
                            </label>
                            <input
                                type="text"
                                id="msgCall"
                                class="z-input"
                                maxlength="300"
                                placeholder="Ex: NÃO ACEITAMOS LIGAÇÃO VIA WHATSAPP!"
                                style="width:100%;"
                            />
                        </div>

                        <div style="margin-top:14px;display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                            <button type="button" class="z-btn z-btn-primary" id="btnSaveSettings">
                                Salvar configurações
                            </button>

                            <button type="button" class="z-btn" id="btnReloadSettings">
                                Recarregar
                            </button>

                            <span style="font-size:12px;color:var(--muted);">
                                (Essas configs são aplicadas direto na Evolution pela API.)
                            </span>
                        </div>
                    </div>
                </div>

            </div>

            <div style="flex:0 0 auto;">
                <div class="z-card">
                    <div class="z-card-header">
                        <strong>QR Code</strong>
                    </div>
                    <div class="z-card-body" style="display:flex;align-items:center;justify-content:center;padding:18px;">
                        @php
                            $qr = $data['qr_data_uri'] ?? null;

                            if (!$qr && isset($data['body']['qrcode']['base64'])) {
                                $qr = 'data:image/png;base64,' . $data['body']['qrcode']['base64'];
                            }
                        @endphp

                        @if ($qr)
                            <img src="{{ $qr }}" alt="QR Code" style="max-width:320px;width:320px;height:auto;border-radius:12px;">
                        @else
                            <div class="z-badge z-badge-off">⛔ Não foi possível gerar QR.</div>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- DEBUG: só aparece se estiver em local E com ?debug=1 --}}
@if (app()->environment('local') && request()->boolean('debug'))
    <div class="z-card" style="margin-top:14px;">
        <div class="z-card-header">
            <strong>Resposta completa (debug)</strong>
        </div>
        <div class="z-card-body" style="padding:0;">
            <pre style="margin:0;padding:14px;max-height:420px;overflow:auto;background:#0b1220;color:#cbd5e1;border-radius:12px;">{{ json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
    </div>
@endif

<script>
(function () {
    // 👉 Se você quiser voltar a redirecionar automaticamente ao conectar, troque para true
    const AUTO_REDIRECT = false;

    const instanceId = @json($instanceId);

    // --- STATUS POLLING ---
    const badge = document.getElementById('statusBadge');
    const hint = document.getElementById('statusHint');
    const statusUrl = @json(route('instancias.status', $instanceId));
    let tries = 0;
    const maxTries = 120; // 120 * 3s = 6 minutos

    function setConnectedUI() {
        badge.className = 'z-badge z-badge-ok';
        badge.textContent = '✅ Conectado';

        if (hint) {
            hint.textContent = 'Conexão ativa. Você pode ajustar o Comportamento abaixo e depois voltar para a lista de instâncias.';
        }
    }

    async function checkStatus() {
        tries++;

        try {
            const res = await fetch(statusUrl, {
                method: 'GET',
                headers: { 'Accept': 'application/json' },
                cache: 'no-store'
            });

            const data = await res.json();
            const state = (data && data.state) ? String(data.state).toLowerCase() : '';

            if (data && (data.connected === true || state === 'open')) {
                setConnectedUI();

                // mantém a tela para poder configurar comportamento
                if (AUTO_REDIRECT) {
                    badge.textContent = '✅ Conectado! Redirecionando...';
                    window.location.href = @json(route('instancias.index'));
                    return;
                }

                // continua verificando menos agressivo (opcional)
                setTimeout(checkStatus, 8000);
                return;
            }

            badge.className = 'z-badge z-badge-warn';
            badge.textContent = '⏳ Aguardando leitura do QR...';

        } catch (e) {
            badge.className = 'z-badge z-badge-warn';
            badge.textContent = '⏳ Aguardando leitura do QR...';
        }

        if (tries < maxTries) setTimeout(checkStatus, 3000);
        else {
            badge.className = 'z-badge z-badge-warn';
            badge.textContent = 'ℹ️ Tempo de espera esgotado. Você pode voltar para Instâncias e tentar novamente.';
        }
    }

    setTimeout(checkStatus, 1500);

    // --- SETTINGS (Comportamento) ---
    const settingsGetUrl = @json(route('instancias.settings.get', $instanceId));
    const settingsSetUrl = @json(route('instancias.settings.set', $instanceId));
    const csrf = @json(csrf_token());

    const elMsg = document.getElementById('settingsMsg');

    const elAlwaysOnline = document.getElementById('alwaysOnline');
    const elGroupsIgnore = document.getElementById('groupsIgnore');
    const elRejectCall = document.getElementById('rejectCall');
    const elReadMessages = document.getElementById('readMessages');
    const elReadStatus = document.getElementById('readStatus');
    const elSyncFullHistory = document.getElementById('syncFullHistory');
    const elMsgCall = document.getElementById('msgCall');

    function showMsg(type, text) {
        if (!elMsg) return;
        elMsg.style.display = 'inline-block';
        elMsg.className = 'z-badge ' + (type === 'ok' ? 'z-badge-ok' : type === 'off' ? 'z-badge-off' : 'z-badge-warn');
        elMsg.textContent = text;
    }

    function toBool(v) {
        if (typeof v === 'boolean') return v;
        if (typeof v === 'number') return v === 1;
        if (typeof v === 'string') return ['1','true','yes','on'].includes(v.toLowerCase());
        return !!v;
    }

    function normalizeSettings(raw) {
        // Pode vir como: { settings: {...} } ou {...} ou { data: {...} } etc.
        let s =
            raw?.settings ??
            raw?.data?.settings ??
            raw?.data ??
            raw?.body?.settings ??
            raw?.body ??
            raw;

        // Algumas respostas vêm com settings dentro de "instance"
        if (s?.instance?.settings) s = s.instance.settings;

        // Se vier string (body não-json), evita quebrar
        if (typeof s === 'string') return {
            alwaysOnline: false,
            groupsIgnore: false,
            rejectCall: false,
            readMessages: false,
            readStatus: false,
            syncFullHistory: false,
            msgCall: ''
        };

        return {
            alwaysOnline: toBool(s?.alwaysOnline ?? s?.always_online),
            groupsIgnore: toBool(s?.groupsIgnore ?? s?.groups_ignore ?? s?.ignoreGroups),
            rejectCall: toBool(s?.rejectCall ?? s?.reject_call),
            readMessages: toBool(s?.readMessages ?? s?.read_messages),
            readStatus: toBool(s?.readStatus ?? s?.read_status),
            syncFullHistory: toBool(s?.syncFullHistory ?? s?.sync_full_history),
            msgCall: (typeof (s?.msgCall ?? s?.msg_call) === 'string') ? (s?.msgCall ?? s?.msg_call) : ''
        };
    }

    async function loadSettings() {
        try {
            showMsg('warn', '⏳ Carregando configurações...');

            const res = await fetch(settingsGetUrl, {
                method: 'GET',
                headers: { 'Accept': 'application/json' },
                cache: 'no-store'
            });

            const json = await res.json();

            if (!json || json.ok !== true) {
                showMsg('warn', '⚠️ Não consegui carregar da Evolution. Você pode salvar e aplicar mesmo assim.');
                return;
            }

            const s = normalizeSettings(json.settings);

            elAlwaysOnline.checked = s.alwaysOnline;
            elGroupsIgnore.checked = s.groupsIgnore;
            elRejectCall.checked = s.rejectCall;
            elReadMessages.checked = s.readMessages;
            elReadStatus.checked = s.readStatus;
            elSyncFullHistory.checked = s.syncFullHistory;
            elMsgCall.value = s.msgCall || '';

            elMsg.style.display = 'none';
        } catch (e) {
            showMsg('warn', '⚠️ Não consegui carregar da Evolution. Você pode salvar e aplicar mesmo assim.');
        }
    }

    async function saveSettings() {
        try {
            showMsg('warn', '⏳ Salvando configurações...');

            const payload = {
                alwaysOnline: elAlwaysOnline.checked,
                groupsIgnore: elGroupsIgnore.checked,
                rejectCall: elRejectCall.checked,
                readMessages: elReadMessages.checked,
                readStatus: elReadStatus.checked,
                syncFullHistory: elSyncFullHistory.checked,
                msgCall: (elMsgCall.value && elMsgCall.value.trim() !== '') ? elMsgCall.value.trim() : null
            };

            const res = await fetch(settingsSetUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf
                },
                body: JSON.stringify(payload)
            });

            const json = await res.json();

            if (json && json.ok === true) {
                showMsg('ok', '✅ Configurações salvas com sucesso!');
                setTimeout(() => { elMsg.style.display = 'none'; }, 2500);
                return;
            }

            showMsg('off', '⛔ Não consegui salvar as configurações na Evolution.');
        } catch (e) {
            showMsg('off', '⛔ Erro ao salvar as configurações.');
        }
    }

    document.getElementById('btnSaveSettings')?.addEventListener('click', saveSettings);
    document.getElementById('btnReloadSettings')?.addEventListener('click', loadSettings);

    // carrega settings ao abrir a tela
    loadSettings();

})();
</script>

@endsection
