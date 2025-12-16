@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 1200px; margin-top: 24px;">
    <div style="background:#fff;border-radius:16px;padding:22px;box-shadow:0 10px 30px rgba(0,0,0,.06);border:1px solid rgba(0,0,0,.06);">

        <div style="display:flex;justify-content:space-between;gap:12px;align-items:flex-end;flex-wrap:wrap;">
            <div>
                <div style="font-size:12px;color:#6b7280;font-weight:800;letter-spacing:.06em;text-transform:uppercase;">Admin</div>
                <h2 style="margin:6px 0 0 0;font-weight:900;">Painel do Sistema</h2>
                <div style="margin-top:6px;color:#6b7280;font-size:13px;">
                    Visão geral por usuário: conexões, status e estabilidade.
                </div>
            </div>
        </div>

        <div style="margin-top:16px;overflow:auto;border-radius:14px;border:1px solid rgba(0,0,0,.07);">
            <table style="width:100%;border-collapse:collapse;min-width:1100px;">
                <thead>
                <tr style="background:#f8fafc;text-align:left;">
                    <th style="padding:12px 12px;font-size:12px;color:#64748b;font-weight:900;">Usuário</th>
                    <th style="padding:12px 12px;font-size:12px;color:#64748b;font-weight:900;text-align:center;">Conexões</th>
                    <th style="padding:12px 12px;font-size:12px;color:#64748b;font-weight:900;text-align:center;">Ativas</th>
                    <th style="padding:12px 12px;font-size:12px;color:#64748b;font-weight:900;text-align:center;">Conectadas</th>
                    <th style="padding:12px 12px;font-size:12px;color:#64748b;font-weight:900;text-align:center;">Descon.</th>
                    <th style="padding:12px 12px;font-size:12px;color:#64748b;font-weight:900;text-align:center;">QR</th>
                    <th style="padding:12px 12px;font-size:12px;color:#64748b;font-weight:900;text-align:center;">Erros (24h)</th>
                    <th style="padding:12px 12px;font-size:12px;color:#64748b;font-weight:900;">Última atividade</th>
                    <th style="padding:12px 12px;font-size:12px;color:#64748b;font-weight:900;text-align:right;">Ações</th>
                </tr>
                </thead>
                <tbody>
                @forelse($rows as $r)
                    @php
                        $last = '-';
                        try {
                            if (!empty($r->last_activity)) {
                                $last = \Carbon\Carbon::parse($r->last_activity)->format('d/m/Y H:i');
                            }
                        } catch (\Throwable $e) {
                            $last = (string) $r->last_activity;
                        }

                        $errors = (int) ($r->errors_24h ?? 0);

                        // Rota: tenta a principal; se não existir, cai em alternativa comum
                        $routeName = null;
                        if (function_exists('route')) {
                            if (\Illuminate\Support\Facades\Route::has('admin.user.instances')) {
                                $routeName = 'admin.user.instances';
                            } elseif (\Illuminate\Support\Facades\Route::has('admin.users.instances')) {
                                $routeName = 'admin.users.instances';
                            }
                        }
                    @endphp

                    <tr style="border-top:1px solid rgba(0,0,0,.06);">
                        <td style="padding:12px 12px;">
                            <div style="font-weight:900;color:#111827;">{{ $r->name }}</div>
                            <div style="font-size:12px;color:#6b7280;">{{ $r->email }}</div>
                        </td>

                        <td style="padding:12px 12px;text-align:center;font-weight:900;">{{ (int)($r->instances_total ?? 0) }}</td>
                        <td style="padding:12px 12px;text-align:center;font-weight:900;">{{ (int)($r->instances_active ?? 0) }}</td>
                        <td style="padding:12px 12px;text-align:center;font-weight:900;">{{ (int)($r->instances_connected ?? 0) }}</td>
                        <td style="padding:12px 12px;text-align:center;font-weight:900;">{{ (int)($r->instances_disconnected ?? 0) }}</td>
                        <td style="padding:12px 12px;text-align:center;font-weight:900;">{{ (int)($r->instances_qrcode ?? 0) }}</td>

                        <td style="padding:12px 12px;text-align:center;">
                            <span style="display:inline-block;padding:4px 10px;border-radius:999px;font-size:12px;font-weight:900;
                                background:{{ $errors > 0 ? '#ffecec' : '#e8fff1' }};
                                border:1px solid {{ $errors > 0 ? '#ffbdbd' : '#b8f1cc' }};
                                color:{{ $errors > 0 ? '#7a1b1b' : '#145a32' }};">
                                {{ $errors }}
                            </span>
                        </td>

                        <td style="padding:12px 12px;color:#334155;">{{ $last }}</td>

                        <td style="padding:12px 12px;text-align:right;">
                            @if($routeName)
                                <a href="{{ route($routeName, $r->id) }}"
                                   style="display:inline-block;padding:8px 10px;border-radius:10px;border:1px solid rgba(59,130,246,.25);
                                   background:rgba(59,130,246,.10);text-decoration:none;font-weight:900;color:#1d4ed8;">
                                    Ver instâncias
                                </a>
                            @else
                                <span style="display:inline-block;padding:8px 10px;border-radius:10px;border:1px solid rgba(148,163,184,.35);
                                background:rgba(148,163,184,.12);font-weight:900;color:#475569;">
                                    Rota não configurada
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="padding:14px 12px;color:#6b7280;">Nenhum usuário encontrado.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div style="margin-top:12px;color:#64748b;font-size:12px;">
            Dica: “Erros (24h)” considera eventos do tipo <code>ERROR</code> registrados nas últimas 24 horas.
        </div>

    </div>
</div>
@endsection
