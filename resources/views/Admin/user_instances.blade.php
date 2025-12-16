@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 1200px; margin-top: 24px;">
    @php
        $instancesTotal = (int) count($instances ?? []);
        $instancesActive = 0;
        $connected = 0;
        $qrcode = 0;
        $disconnected = 0;
        $errors = 0;

        foreach (($instances ?? []) as $it) {
            $instancesActive += ((int)($it->is_active ?? 0) === 1) ? 1 : 0;

            $ev = strtoupper((string)($it->last_event ?? ''));
            if ($ev === 'CONNECTED') $connected++;
            elseif ($ev === 'QRCODE') $qrcode++;
            elseif ($ev === 'DISCONNECTED') $disconnected++;
            elseif ($ev === 'ERROR') $errors++;
        }

        $chip = function(string $label, $value, string $bg, string $bd, string $tx) {
            return '<span style="
                display:inline-flex;align-items:center;gap:8px;
                padding:7px 10px;border-radius:999px;
                background:'.$bg.';border:1px solid '.$bd.';
                font-size:12px;font-weight:900;color:'.$tx.';
            "><span style="opacity:.8;font-weight:800;">'.$label.':</span> <span>'.$value.'</span></span>';
        };

        $badgeForEvent = function(?string $event) {
            $event = strtoupper((string)$event);

            $bg = '#f3f4f6'; $bd = '#e5e7eb'; $tx = '#374151';

            if ($event === 'CONNECTED') { $bg='#e8fff1'; $bd='#b8f1cc'; $tx='#145a32'; }
            elseif ($event === 'QRCODE') { $bg='#fff7e6'; $bd='#ffe0a3'; $tx='#7a4b00'; }
            elseif ($event === 'DISCONNECTED') { $bg='#ffecec'; $bd='#ffbdbd'; $tx='#7a1b1b'; }
            elseif ($event === 'ERROR') { $bg='#ffecec'; $bd='#ffbdbd'; $tx='#7a1b1b'; }

            $label = $event !== '' ? $event : '-';

            return '<span style="
                display:inline-block;padding:4px 10px;border-radius:999px;
                font-size:12px;font-weight:900;
                background:'.$bg.';border:1px solid '.$bd.';color:'.$tx.';
            ">'.$label.'</span>';
        };

        $badgeForActive = function(bool $isActive) {
            $bg = $isActive ? '#e7f1ff' : '#f3f4f6';
            $bd = $isActive ? '#cfe2ff' : '#e5e7eb';
            $tx = $isActive ? '#1d4ed8' : '#374151';
            return '<span style="
                display:inline-block;padding:4px 10px;border-radius:999px;
                font-size:12px;font-weight:900;
                background:'.$bg.';border:1px solid '.$bd.';color:'.$tx.';
            ">'.($isActive ? 'SIM' : 'NÃO').'</span>';
        };
    @endphp

    <div style="display:flex;justify-content:space-between;align-items:flex-end;gap:12px;flex-wrap:wrap;">
        <div>
            <div style="font-size:12px;color:#6b7280;font-weight:800;letter-spacing:.06em;text-transform:uppercase;">
                Admin • Usuário
            </div>
            <h2 style="margin:6px 0 0 0;font-weight:900;">
                {{ $user->name ?? 'Usuário' }} — Instâncias
            </h2>
            <div style="margin-top:6px;color:#6b7280;font-size:13px;">
                {{ $user->email ?? '' }}
            </div>

            <div style="margin-top:12px;display:flex;flex-wrap:wrap;gap:8px;">
                {!! $chip('Total', $instancesTotal, '#ffffff', 'rgba(0,0,0,.08)', '#111827') !!}
                {!! $chip('Ativas', $instancesActive, 'rgba(59,130,246,.10)', 'rgba(59,130,246,.22)', '#1d4ed8') !!}
                {!! $chip('Conectadas', $connected, '#e8fff1', '#b8f1cc', '#145a32') !!}
                {!! $chip('QR', $qrcode, '#fff7e6', '#ffe0a3', '#7a4b00') !!}
                {!! $chip('Desconect.', $disconnected, '#ffecec', '#ffbdbd', '#7a1b1b') !!}
                {!! $chip('Erros', $errors, '#ffecec', '#ffbdbd', '#7a1b1b') !!}
            </div>
        </div>

        <a href="{{ route('admin.dashboard') }}"
           style="padding:8px 12px;border-radius:10px;border:1px solid #e5e7eb;background:#f8fafc;text-decoration:none;font-weight:900;color:#111827;">
            ← Voltar
        </a>
    </div>

    <div style="margin-top:14px;background:#fff;border-radius:16px;padding:18px;border:1px solid rgba(0,0,0,.06);box-shadow:0 10px 30px rgba(0,0,0,.06);">
        <div style="overflow:auto;border-radius:14px;border:1px solid rgba(0,0,0,.07);">
            <table style="width:100%;border-collapse:collapse;min-width:1040px;">
                <thead>
                <tr style="background:#f8fafc;text-align:left;">
                    <th style="padding:12px;font-size:12px;color:#64748b;font-weight:900;">Label</th>
                    <th style="padding:12px;font-size:12px;color:#64748b;font-weight:900;">Instance name</th>
                    <th style="padding:12px;font-size:12px;color:#64748b;font-weight:900;text-align:center;">Ativa</th>
                    <th style="padding:12px;font-size:12px;color:#64748b;font-weight:900;">Último evento</th>
                    <th style="padding:12px;font-size:12px;color:#64748b;font-weight:900;">Detalhe</th>
                    <th style="padding:12px;font-size:12px;color:#64748b;font-weight:900;">Quando</th>
                    <th style="padding:12px;font-size:12px;color:#64748b;font-weight:900;text-align:right;">Ações</th>
                </tr>
                </thead>
                <tbody>
                @foreach($instances as $i)
                    @php
                        $last = $i->last_event_at ? \Carbon\Carbon::parse($i->last_event_at)->format('d/m/Y H:i') : '-';
                        $isActive = (int)($i->is_active ?? 0) === 1;

                        $ev = (string)($i->last_event ?? '');
                        $status = (string)($i->last_status ?? '');
                        $msg = (string)($i->last_message ?? '');

                        $detail = trim(($status !== '' ? $status : '') . ($msg !== '' ? ' • ' . $msg : ''));
                        if (mb_strlen($detail) > 90) $detail = mb_substr($detail, 0, 90) . '…';
                    @endphp

                    <tr style="border-top:1px solid rgba(0,0,0,.06);">
                        <td style="padding:12px;">
                            <div style="font-weight:900;color:#111827;">{{ $i->label ?? '-' }}</div>
                            <div style="font-size:12px;color:#6b7280;">ID #{{ $i->id }}</div>
                        </td>

                        <td style="padding:12px;color:#334155;">
                            <div style="font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;">
                                {{ $i->instance_name ?? '-' }}
                            </div>
                        </td>

                        <td style="padding:12px;text-align:center;">
                            {!! $badgeForActive($isActive) !!}
                        </td>

                        <td style="padding:12px;">
                            {!! $badgeForEvent($ev) !!}
                        </td>

                        <td style="padding:12px;color:#334155;max-width:420px;">
                            <span style="color:#64748b;">{{ $detail !== '' ? $detail : '-' }}</span>
                        </td>

                        <td style="padding:12px;color:#334155;">{{ $last }}</td>

                        <td style="padding:12px;text-align:right;">
                            <a href="{{ route('admin.instance.events', $i->id) }}"
                               style="display:inline-block;padding:8px 10px;border-radius:10px;
                               border:1px solid rgba(59,130,246,.25);
                               background:rgba(59,130,246,.10);
                               text-decoration:none;font-weight:900;color:#1d4ed8;">
                                Ver eventos
                            </a>
                        </td>
                    </tr>
                @endforeach

                @if(count($instances) === 0)
                    <tr>
                        <td colspan="7" style="padding:14px 12px;color:#6b7280;">
                            Nenhuma instância cadastrada.
                        </td>
                    </tr>
                @endif
                </tbody>
            </table>
        </div>

        <div style="margin-top:10px;color:#6b7280;font-size:12px;">
            Dica: “Último evento” é baseado no registro mais recente em <code>whatsapp_instance_events</code>.
        </div>
    </div>
</div>
@endsection
