@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 1200px; margin-top: 24px;">
    @php
        $instanceName = $instance->instance_name ?? '-';
        $label = $instance->label ?? null;

        $backUrl = url()->previous();
        // se veio direto (ou previous estranho), manda pro dashboard
        if (!$backUrl || $backUrl === url()->current()) {
            $backUrl = route('admin.dashboard');
        }

        $badgeEvent = function($event) {
            $ev = strtoupper((string)$event);

            $bg = '#f3f4f6'; $bd = '#e5e7eb'; $tx = '#374151';

            if ($ev === 'CONNECTED') { $bg='#e8fff1'; $bd='#b8f1cc'; $tx='#145a32'; }
            elseif ($ev === 'QRCODE') { $bg='#fff7e6'; $bd='#ffe0a3'; $tx='#7a4b00'; }
            elseif ($ev === 'DISCONNECTED') { $bg='#ffecec'; $bd='#ffbdbd'; $tx='#7a1b1b'; }
            elseif ($ev === 'ERROR') { $bg='#ffecec'; $bd='#ffbdbd'; $tx='#7a1b1b'; }

            return '<span style="
                display:inline-block;padding:4px 10px;border-radius:999px;
                font-size:12px;font-weight:900;
                background:'.$bg.';border:1px solid '.$bd.';color:'.$tx.';
                white-space:nowrap;
            ">'.$ev.'</span>';
        };

        $badgeStatus = function($status) {
            $st = strtoupper((string)$status);
            if ($st === '') $st = '-';

            $bg = '#f3f4f6'; $bd = '#e5e7eb'; $tx = '#374151';

            // tenta interpretar status comuns
            if (in_array($st, ['OK','SUCCESS','CONNECTED','ONLINE'], true)) { $bg='#e8fff1'; $bd='#b8f1cc'; $tx='#145a32'; }
            elseif (in_array($st, ['WARN','WARNING','QRCODE'], true)) { $bg='#fff7e6'; $bd='#ffe0a3'; $tx='#7a4b00'; }
            elseif (in_array($st, ['ERR','ERROR','FAILED','DISCONNECTED','OFFLINE'], true)) { $bg='#ffecec'; $bd='#ffbdbd'; $tx='#7a1b1b'; }

            return '<span style="
                display:inline-block;padding:4px 10px;border-radius:999px;
                font-size:12px;font-weight:900;
                background:'.$bg.';border:1px solid '.$bd.';color:'.$tx.';
                white-space:nowrap;
            ">'.$st.'</span>';
        };
    @endphp

    <div style="display:flex;justify-content:space-between;align-items:flex-end;gap:12px;flex-wrap:wrap;">
        <div>
            <div style="font-size:12px;color:#6b7280;font-weight:800;letter-spacing:.06em;text-transform:uppercase;">
                Admin • Instância
            </div>

            <h2 style="margin:6px 0 0 0;font-weight:900;">
                Eventos — {{ $instanceName }}
            </h2>

            <div style="margin-top:6px;color:#6b7280;font-size:13px;display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
                <span>Últimos 200 eventos (auditoria/diagnóstico).</span>
                @if($label)
                    <span style="display:inline-block;padding:4px 10px;border-radius:999px;background:#f8fafc;border:1px solid #e5e7eb;font-size:12px;font-weight:900;color:#111827;">
                        {{ $label }}
                    </span>
                @endif
            </div>
        </div>

        <a href="{{ $backUrl }}"
           style="padding:8px 12px;border-radius:10px;border:1px solid #e5e7eb;background:#f8fafc;text-decoration:none;font-weight:900;color:#111827;">
            ← Voltar
        </a>
    </div>

    {{-- Barra de filtro (front-end, sem mexer no backend) --}}
    <div style="margin-top:12px;display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
        <div style="flex:1;min-width:260px;">
            <input id="evtSearch" type="text" class="form-control"
                   placeholder="Filtrar (evento, status, source, mensagem)...">
        </div>

        <div style="min-width:220px;">
            <select id="evtType" class="form-control">
                <option value="">Todos os eventos</option>
                <option value="CONNECTED">CONNECTED</option>
                <option value="QRCODE">QRCODE</option>
                <option value="DISCONNECTED">DISCONNECTED</option>
                <option value="ERROR">ERROR</option>
            </select>
        </div>

        <div style="min-width:160px;">
            <button id="evtClear" type="button" class="btn btn-outline-secondary" style="font-weight:900;width:100%;">
                Limpar
            </button>
        </div>
    </div>

    <div style="margin-top:14px;background:#fff;border-radius:16px;padding:18px;border:1px solid rgba(0,0,0,.06);box-shadow:0 10px 30px rgba(0,0,0,.06);">
        <div style="overflow:auto;border-radius:14px;border:1px solid rgba(0,0,0,.07);">
            <table style="width:100%;border-collapse:collapse;min-width:1040px;">
                <thead>
                <tr style="background:#f8fafc;text-align:left;">
                    <th style="padding:12px;font-size:12px;color:#64748b;font-weight:900;white-space:nowrap;">Data</th>
                    <th style="padding:12px;font-size:12px;color:#64748b;font-weight:900;white-space:nowrap;">Evento</th>
                    <th style="padding:12px;font-size:12px;color:#64748b;font-weight:900;white-space:nowrap;">Status</th>
                    <th style="padding:12px;font-size:12px;color:#64748b;font-weight:900;white-space:nowrap;">Fonte</th>
                    <th style="padding:12px;font-size:12px;color:#64748b;font-weight:900;">Mensagem</th>
                </tr>
                </thead>
                <tbody id="evtBody">
                @foreach($events as $e)
                    @php
                        $dt = $e->created_at ? \Carbon\Carbon::parse($e->created_at)->format('d/m/Y H:i:s') : '-';
                        $event = (string)($e->event ?? '');
                        $status = (string)($e->status ?? '');
                        $source = (string)($e->source ?? '');

                        $msg = (string)($e->message ?? '');
                        $msgTrim = trim($msg);
                        if (mb_strlen($msgTrim) > 240) $msgTrim = mb_substr($msgTrim, 0, 240) . '…';

                        // texto para busca (sem HTML)
                        $searchText = strtoupper(trim($dt.' '.$event.' '.$status.' '.$source.' '.$msg));
                    @endphp

                    <tr class="evtRow"
                        data-event="{{ strtoupper($event) }}"
                        data-search="{{ e($searchText) }}"
                        style="border-top:1px solid rgba(0,0,0,.06);">
                        <td style="padding:12px;color:#334155;white-space:nowrap;">
                            {{ $dt }}
                        </td>

                        <td style="padding:12px;">
                            {!! $badgeEvent($event) !!}
                        </td>

                        <td style="padding:12px;">
                            {!! $badgeStatus($status) !!}
                        </td>

                        <td style="padding:12px;color:#334155;">
                            <span style="display:inline-block;padding:4px 10px;border-radius:999px;background:#f8fafc;border:1px solid #e5e7eb;font-size:12px;font-weight:900;color:#111827;">
                                {{ $source !== '' ? $source : '-' }}
                            </span>
                        </td>

                        <td style="padding:12px;color:#334155;max-width:560px;">
                            <div style="white-space:pre-wrap;word-break:break-word;line-height:1.35;">
                                {{ $msgTrim !== '' ? $msgTrim : '-' }}
                            </div>
                        </td>
                    </tr>
                @endforeach

                @if(count($events) === 0)
                    <tr><td colspan="5" style="padding:14px 12px;color:#6b7280;">Sem eventos.</td></tr>
                @endif
                </tbody>
            </table>
        </div>

        <div id="evtEmpty" style="display:none;margin-top:10px;color:#6b7280;font-size:12px;">
            Nenhum evento encontrado com esse filtro.
        </div>
    </div>
</div>

<script>
(function() {
    const input = document.getElementById('evtSearch');
    const type = document.getElementById('evtType');
    const clear = document.getElementById('evtClear');
    const rows = Array.from(document.querySelectorAll('.evtRow'));
    const empty = document.getElementById('evtEmpty');

    function apply() {
        const q = (input.value || '').trim().toUpperCase();
        const t = (type.value || '').trim().toUpperCase();

        let visible = 0;

        rows.forEach(r => {
            const ev = (r.getAttribute('data-event') || '').toUpperCase();
            const s = (r.getAttribute('data-search') || '').toUpperCase();

            const okType = !t || ev === t;
            const okSearch = !q || s.includes(q);

            const show = okType && okSearch;
            r.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        empty.style.display = (visible === 0 && rows.length > 0) ? '' : 'none';
    }

    input && input.addEventListener('input', apply);
    type && type.addEventListener('change', apply);
    clear && clear.addEventListener('click', function() {
        input.value = '';
        type.value = '';
        apply();
    });

    apply();
})();
</script>
@endsection
