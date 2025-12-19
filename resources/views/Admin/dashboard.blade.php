@extends('layouts.app')

@section('content')

<div class="panel-header">
    <div>
        <div style="font-size:12px;color:var(--muted);font-weight:900;letter-spacing:.08em;text-transform:uppercase;">
            Admin
        </div>
        <h1 class="panel-title" style="margin-top:6px;">Painel do Sistema</h1>
        <p class="panel-subtitle">Visão geral por usuário: conexões, status e estabilidade.</p>
    </div>
</div>

<div class="z-card">
    <div class="z-card-header">
        <strong>Resumo por usuário</strong>
        <span style="font-size:12px;color:var(--muted);">Conexões e eventos das últimas 24h</span>
    </div>

    <div class="z-card-body" style="padding:0;">
        <div style="overflow:auto;">
            <table class="z-table" style="min-width:1100px;">
                <thead>
                    <tr>
                        <th>Usuário</th>
                        <th style="text-align:center;">Conexões</th>
                        <th style="text-align:center;">Ativas</th>
                        <th style="text-align:center;">Conectadas</th>
                        <th style="text-align:center;">Descon.</th>
                        <th style="text-align:center;">QR</th>
                        <th style="text-align:center;">Erros (24h)</th>
                        <th>Última atividade</th>
                        <th style="text-align:right;">Ações</th>
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

                        // Badge erros
                        $errorBadge = $errors > 0 ? 'z-badge z-badge-off' : 'z-badge z-badge-ok';

                        // Rota: tenta a principal; se não existir, cai em alternativa comum
                        $routeName = null;
                        try {
                            if (\Illuminate\Support\Facades\Route::has('admin.user.instances')) {
                                $routeName = 'admin.user.instances';
                            } elseif (\Illuminate\Support\Facades\Route::has('admin.users.instances')) {
                                $routeName = 'admin.users.instances';
                            }
                        } catch (\Throwable $e) {
                            $routeName = null;
                        }
                    @endphp

                    <tr>
                        <td>
                            <div style="font-weight:900;color:rgba(229,231,235,.92);">{{ $r->name }}</div>
                            <div style="font-size:12px;color:var(--muted);">{{ $r->email }}</div>
                        </td>

                        <td style="text-align:center;font-weight:900;">{{ (int)($r->instances_total ?? 0) }}</td>
                        <td style="text-align:center;font-weight:900;">{{ (int)($r->instances_active ?? 0) }}</td>
                        <td style="text-align:center;font-weight:900;">{{ (int)($r->instances_connected ?? 0) }}</td>
                        <td style="text-align:center;font-weight:900;">{{ (int)($r->instances_disconnected ?? 0) }}</td>
                        <td style="text-align:center;font-weight:900;">{{ (int)($r->instances_qrcode ?? 0) }}</td>

                        <td style="text-align:center;">
                            <span class="{{ $errorBadge }}">{{ $errors }}</span>
                        </td>

                        <td style="color:rgba(229,231,235,.92);">
                            {{ $last }}
                        </td>

                        <td style="text-align:right;">
                            @if($routeName)
                                <a class="z-btn" href="{{ route($routeName, $r->id) }}">
                                    Ver instâncias
                                </a>
                            @else
                                <span class="z-badge">
                                    Rota não configurada
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" style="padding:16px 12px;color:var(--muted);">
                            Nenhum usuário encontrado.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<div style="margin-top:12px;color:var(--muted);font-size:12px;">
    Dica: “Erros (24h)” considera eventos do tipo <code>ERROR</code> registrados nas últimas 24 horas.
</div>

@endsection
