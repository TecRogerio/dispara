@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 980px; margin-top: 30px;">
    <div style="background:#fff;border-radius:14px;padding:28px;box-shadow:0 6px 18px rgba(0,0,0,.06);">

        <div style="display:flex;justify-content:space-between;align-items:center;gap:15px;flex-wrap:wrap;">
            <h2 style="margin:0;font-weight:700;">Minhas Instâncias (WhatsApp)</h2>

            <a href="{{ route('instancias.create') }}" style="font-weight:600;">
                + Nova instância
            </a>
        </div>

        @if (session('success'))
            <div style="margin-top:16px;padding:12px 14px;border-radius:10px;background:#e8fff1;border:1px solid #b8f1cc;color:#145a32;">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div style="margin-top:16px;padding:12px 14px;border-radius:10px;background:#ffecec;border:1px solid #ffbdbd;color:#7a1b1b;">
                {{ session('error') }}
            </div>
        @endif

        @if (session('info'))
            <div style="margin-top:16px;padding:12px 14px;border-radius:10px;background:#eef6ff;border:1px solid #b9dbff;color:#0b3a66;">
                {{ session('info') }}
            </div>
        @endif

        <div style="margin-top:18px;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="text-align:left;border-bottom:1px solid #eee;">
                        <th style="padding:10px 8px;">Label</th>
                        <th style="padding:10px 8px;">Instance</th>
                        <th style="padding:10px 8px;">Ativa</th>
                        <th style="padding:10px 8px;">Limite/dia</th>
                        <th style="padding:10px 8px;">Status (Evo)</th>
                        <th style="padding:10px 8px;">Ações</th>
                    </tr>
                </thead>

                <tbody>
                @forelse ($instances as $row)
                    <tr style="border-bottom:1px solid #f2f2f2;">
                        <td style="padding:12px 8px;">
                            {{ $row->label ?? '-' }}
                        </td>

                        <td style="padding:12px 8px;">
                            <strong>{{ $row->instance_name }}</strong>
                        </td>

                        <td style="padding:12px 8px;">
                            @if ((bool)$row->enabled)
                                <span style="display:inline-block;padding:4px 10px;border-radius:999px;background:#e8fff1;border:1px solid #b8f1cc;color:#145a32;font-size:12px;">
                                    SIM
                                </span>
                            @else
                                <span style="display:inline-block;padding:4px 10px;border-radius:999px;background:#fff3cd;border:1px solid #ffe69c;color:#6b4f00;font-size:12px;">
                                    NÃO
                                </span>
                            @endif
                        </td>

                        <td style="padding:12px 8px;">
                            {{ (int)($row->daily_limit ?? 200) }}
                        </td>

                        <td style="padding:12px 8px;">
                            <span style="font-size:12px;color:#999;">-</span>
                        </td>

                        <td style="padding:12px 8px;">
                            <div style="display:flex;gap:8px;flex-wrap:wrap;">

                                {{-- Conectar (gera QR / inicia sessão na Evolution) --}}
                                <form method="POST" action="{{ route('instancias.connect', $row->id) }}">
                                    @csrf
                                    <button type="submit"
                                        style="padding:6px 10px;border-radius:8px;border:1px solid #cfe2ff;background:#e7f1ff;cursor:pointer;">
                                        Conectar
                                    </button>
                                </form>

                                {{-- Ativar/Desativar (enabled) --}}
                                <form method="POST" action="{{ route('instancias.toggle', $row->id) }}">
                                    @csrf
                                    <button type="submit"
                                        style="padding:6px 10px;border-radius:8px;border:1px solid #ddd;background:#f7f7f7;cursor:pointer;">
                                        {{ ((bool)$row->enabled) ? 'Desativar' : 'Ativar' }}
                                    </button>
                                </form>

                                {{-- Remover --}}
                                <form method="POST" action="{{ route('instancias.destroy', $row->id) }}" onsubmit="return confirm('Remover esta instância?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                        style="padding:6px 10px;border-radius:8px;border:1px solid #ffbdbd;background:#ffecec;cursor:pointer;">
                                        Remover
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="padding:16px 8px;color:#666;">
                            Nenhuma instância cadastrada ainda. Clique em <strong>+ Nova instância</strong>.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        @if (app()->environment('local'))
            <div style="margin-top:18px;font-size:12px;color:#666;">
                Debug local: <a href="{{ url('/evolution/ping') }}">/evolution/ping</a> · <a href="{{ url('/evolution/check') }}">/evolution/check</a>
            </div>
        @endif

    </div>
</div>
@endsection
