@extends('layouts.app')

@section('content')
<div class="container" style="max-width: 920px; margin-top: 30px;">
    <div style="background:#fff;border-radius:14px;padding:28px;box-shadow:0 6px 18px rgba(0,0,0,.06);">

        <div style="display:flex;justify-content:space-between;align-items:center;gap:15px;flex-wrap:wrap;">
            <div>
                <h2 style="margin:0;font-weight:700;">Conectar Instância</h2>
                <div style="margin-top:6px;color:#666;font-size:14px;">
                    <strong>Instance:</strong> {{ $instanceName ?? '-' }}
                </div>
            </div>

            <a href="{{ route('instancias.index') }}" style="font-weight:600;">
                ← Voltar
            </a>
        </div>

        @if (!empty($error))
            <div style="margin-top:16px;padding:12px 14px;border-radius:10px;background:#ffecec;border:1px solid #ffbdbd;color:#7a1b1b;">
                {{ $error }}
            </div>
        @endif

        <div style="margin-top:18px;padding:14px;border-radius:12px;background:#f7f9ff;border:1px solid #e6ecff;">
            <div style="font-weight:700;margin-bottom:8px;">Passos</div>
            <ol style="margin:0;padding-left:18px;color:#444;">
                <li>Abra o WhatsApp no celular</li>
                <li>Vá em <strong>Aparelhos conectados</strong></li>
                <li>Toque em <strong>Conectar um aparelho</strong></li>
                <li>Aponte a câmera para o QR abaixo</li>
            </ol>
        </div>

        @php
            // Agora o EvolutionService já tenta devolver isso pronto:
            $qrDataUri = $data['qr_data_uri'] ?? null;
            $qrBase64  = $data['qr_base64'] ?? null;

            // fallback: se veio em body de algum jeito inesperado
            if (!$qrDataUri && is_array($data['body'] ?? null)) {
                $qrBase64 = $qrBase64
                    ?? ($data['body']['base64'] ?? null)
                    ?? ($data['body']['qrcode']['base64'] ?? null)
                    ?? ($data['body']['qrcode'] ?? null);
            }

            if (!$qrDataUri && is_string($qrBase64) && $qrBase64 !== '') {
                if (stripos($qrBase64, 'data:image') === 0) {
                    $qrDataUri = $qrBase64;
                } else {
                    $qrDataUri = 'data:image/png;base64,' . $qrBase64;
                }
            }

            $state = $data['body']['instance']['state'] ?? $data['body']['state'] ?? null;
        @endphp

        <div style="margin-top:20px;display:grid;grid-template-columns:1fr;gap:16px;">
            <div style="padding:16px;border:1px solid #eee;border-radius:12px;">
                <div style="font-weight:700;margin-bottom:10px;">QR Code</div>

                @if (is_string($state) && strtolower($state) === 'open')
                    <div style="color:#145a32;background:#e8fff1;border:1px solid #b8f1cc;padding:12px;border-radius:10px;">
                        Essa instância já está <strong>conectada</strong> ✅
                    </div>

                @elseif ($qrDataUri)
                    <div style="display:flex;justify-content:center;">
                        <img src="{{ $qrDataUri }}" alt="QR Code" style="max-width:360px;width:100%;border-radius:12px;border:1px solid #eee;">
                    </div>
                    <div style="margin-top:10px;color:#666;font-size:13px;">
                        Depois de escanear, clique em <strong>Já conectei</strong> para voltar à lista.
                    </div>

                @else
                    <div style="color:#777;">
                        Não encontrei QR Code no retorno. A instância pode já estar conectada ou o endpoint retornou outro formato.
                    </div>
                @endif
            </div>

            <div style="padding:16px;border:1px solid #eee;border-radius:12px;">
                <div style="font-weight:700;margin-bottom:10px;">Resposta completa (debug)</div>
                <div style="background:#0b1020;color:#dbe7ff;padding:12px;border-radius:10px;overflow:auto;">
                    <pre style="margin:0;white-space:pre-wrap;word-break:break-word;">{{ json_encode($data ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>
        </div>

        <div style="margin-top:16px;display:flex;gap:10px;flex-wrap:wrap;">
            {{-- Re-bater no connect: se já estiver open, o controller redireciona pra lista --}}
            <form method="POST" action="{{ route('instancias.connect', $instanceId ?? 0) }}">
                @csrf
                <button type="submit" style="padding:8px 12px;border-radius:10px;border:1px solid #cfe2ff;background:#e7f1ff;cursor:pointer;">
                    Já conectei (verificar e voltar)
                </button>
            </form>

            <a href="{{ route('instancias.index') }}" style="padding:8px 12px;border-radius:10px;border:1px solid #ddd;background:#f7f7f7;text-decoration:none;">
                Voltar para Instâncias
            </a>
        </div>

    </div>
</div>
@endsection
