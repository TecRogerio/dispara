<?php

namespace App\Http\Controllers;

use App\Models\WhatsappInstance;
use App\Models\Campaign;
use App\Services\EvolutionService;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WhatsappInstanceController extends Controller
{
    /**
     * Lista instâncias do usuário logado
     */
    public function index()
    {
        $user = Auth::user();

        $instances = WhatsappInstance::where('user_id', $user->id)
            ->orderByDesc('id')
            ->get();

        return view('instancias.index', compact('instances'));
    }

    /**
     * Form de nova instância
     */
    public function create()
    {
        $user = Auth::user();

        $count = WhatsappInstance::where('user_id', $user->id)->count();
        if ($count >= 3) {
            return redirect()->route('instancias.index')
                ->with('error', 'Limite atingido: sua empresa pode conectar no máximo 3 WhatsApps.');
        }

        return view('instancias.create');
    }

    /**
     * Salva nova instância
     */
    public function store(Request $request, EvolutionService $evo)
    {
        $user = Auth::user();

        $count = WhatsappInstance::where('user_id', $user->id)->count();
        if ($count >= 3) {
            return back()->withInput()->withErrors([
                'instance_name' => 'Limite atingido: sua empresa pode conectar no máximo 3 WhatsApps.',
            ]);
        }

        $data = $request->validate([
            'label' => ['required', 'string', 'max:120'],
            'instance_name' => ['required', 'string', 'max:120', 'regex:/^[a-zA-Z0-9._-]+$/'],
            'daily_limit' => ['required', 'integer', 'min:1', 'max:200'],
        ], [
            'instance_name.regex' => 'Instance name inválido. Use apenas letras, números, ponto, hífen (-) ou underscore (_), sem espaços.',
        ]);

        $exists = WhatsappInstance::where('user_id', $user->id)
            ->where('instance_name', $data['instance_name'])
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'instance_name' => 'Essa instância já está cadastrada para sua empresa.',
            ]);
        }

        $token = Str::random(64);

        $create = $evo->createInstance($data['instance_name'], $token, true);

        if (!($create['ok'] ?? false)) {
            $msg = $create['message'] ?? $create['error'] ?? 'Falha ao criar instância na Evolution.';
            return back()->withInput()->withErrors([
                'instance_name' => $msg,
            ]);
        }

        $instance = WhatsappInstance::create([
            'user_id'         => $user->id,
            'label'           => $data['label'],
            'instance_name'   => $data['instance_name'],
            'token'           => $token,
            'enabled'         => true,
            'daily_limit'     => (int) $data['daily_limit'],
            'sent_today'      => 0,
            'sent_today_date' => null,
        ]);

        $hook = $this->configureEvolutionWebhook($instance);
        if (!($hook['ok'] ?? false)) {
            Log::warning('Falha ao configurar webhook da Evolution (store)', [
                'instance' => $instance->instance_name,
                'error'    => $hook['message'] ?? $hook['error'] ?? null,
                'tries'    => $hook['tries'] ?? null,
            ]);

            return redirect()->route('instancias.index')->with(
                'success',
                'Instância criada e salva. Agora clique em "Conectar" para gerar o QR. (Obs: webhook não foi configurado automaticamente — em localhost isso é esperado sem túnel.)'
            );
        }

        return redirect()->route('instancias.index')
            ->with('success', 'Instância criada e salva. Agora clique em "Conectar" para gerar o QR.');
    }

    /**
     * Conecta instância (gera QR / inicia sessão)
     */
    public function connect($id, EvolutionService $evo)
    {
        $user = Auth::user();

        $instance = WhatsappInstance::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        $resp = $evo->connect($instance->instance_name, $instance->token);

        if (!($resp['ok'] ?? false)) {
            $msg = $resp['message'] ?? $resp['error'] ?? 'Falha ao conectar/gerar QR na Evolution.';
            return redirect()->route('instancias.index')->with('error', $msg);
        }

        $hook = $this->configureEvolutionWebhook($instance);
        if (!($hook['ok'] ?? false)) {
            Log::warning('Falha ao configurar webhook da Evolution (connect)', [
                'instance' => $instance->instance_name,
                'error'    => $hook['message'] ?? $hook['error'] ?? null,
                'tries'    => $hook['tries'] ?? null,
            ]);
        }

        $state = $resp['body']['instance']['state'] ?? $resp['body']['state'] ?? null;
        if (is_string($state) && strtolower($state) === 'open') {
            $msg = 'Instância conectada com sucesso ✅';
            if (!($hook['ok'] ?? false)) {
                $msg .= ' (Obs: webhook não foi configurado automaticamente — confira no Manager ou use túnel no localhost.)';
            }
            return redirect()->route('instancias.index')->with('success', $msg);
        }

        $qrDataUri = $resp['qr_data_uri'] ?? null;
        if (is_string($qrDataUri) && $qrDataUri !== '') {
            return view('instancias.qrcode', [
                'instanceId'   => $instance->id,
                'instanceName' => $instance->instance_name,
                'data'         => $resp,
                'error'        => null,
                'webhook_ok'   => (bool)($hook['ok'] ?? false),
            ]);
        }

        $msg = 'A Evolution respondeu, mas não retornou QR. Verifique no Manager se a instância já está conectada.';
        if (!($hook['ok'] ?? false)) {
            $msg .= ' (Obs: webhook não foi configurado automaticamente.)';
        }

        return redirect()->route('instancias.index')->with('info', $msg);
    }

    /**
     * Status da conexão (polling)
     */
    public function status($id)
    {
        $user = Auth::user();

        $instance = WhatsappInstance::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        $base    = rtrim((string) config('evolution.base_url'), '/');
        $apiKey  = (string) config('evolution.api_key');
        $timeout = (int) config('evolution.timeout', 15);

        if ($base === '' || $apiKey === '') {
            return response()->json([
                'ok' => false,
                'connected' => false,
                'state' => null,
                'message' => 'Evolution não configurada (base_url/api_key).',
            ], 200);
        }

        try {
            $candidates = [
                $base . '/instance/connectionState/' . urlencode($instance->instance_name),
                $base . '/instance/connection-state/' . urlencode($instance->instance_name),
                $base . '/instance/connectionState?instanceName=' . urlencode($instance->instance_name),
                $base . '/instance/connection-state?instanceName=' . urlencode($instance->instance_name),
            ];

            $lastJson = null;
            $lastOk   = false;
            $state    = null;

            foreach ($candidates as $url) {
                $resp = Http::timeout($timeout)
                    ->withHeaders(['apikey' => $apiKey, 'Accept' => 'application/json'])
                    ->get($url);

                $lastOk   = $resp->successful();
                $lastJson = $resp->json();

                $state =
                    $lastJson['instance']['state'] ??
                    $lastJson['state'] ??
                    $lastJson['body']['instance']['state'] ??
                    $lastJson['body']['state'] ??
                    null;

                if ($lastOk && $state !== null) {
                    break;
                }
            }

            $connected = is_string($state) && strtolower($state) === 'open';

            return response()->json([
                'ok' => (bool) $lastOk,
                'connected' => $connected,
                'state' => $state,
            ], 200);
        } catch (\Throwable $e) {
            return response()->json([
                'ok' => false,
                'connected' => false,
                'state' => null,
            ], 200);
        }
    }

    /**
     * ✅ Desconectar (logout) da Evolution
     * (agora usando EvolutionService, sem duplicar tentativa de endpoints aqui)
     */
    public function disconnect($id, EvolutionService $evo)
    {
        $user = Auth::user();

        $instance = WhatsappInstance::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        $resp = $evo->disconnectInstance($instance->instance_name, $instance->token);

        if ($resp['ok'] ?? false) {
            return redirect()->route('instancias.index')
                ->with('success', 'Instância desconectada da Evolution/WhatsApp com sucesso ✅');
        }

        Log::warning('Falha ao desconectar instância na Evolution (service)', [
            'instance' => $instance->instance_name,
            'resp' => $resp,
        ]);

        $msg = $resp['message'] ?? $resp['error'] ?? 'Não consegui desconectar na Evolution. (Verifique a versão/endpoint no Manager.)';
        return redirect()->route('instancias.index')->with('error', $msg);
    }

    /**
     * Ativar / Desativar instância no seu sistema
     */
    public function toggle($id)
    {
        $user = Auth::user();

        $instance = WhatsappInstance::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        $instance->enabled = !((bool) $instance->enabled);
        $instance->save();

        return redirect()->route('instancias.index')
            ->with('success', 'Status da instância atualizado.');
    }

    /**
     * Remove instância do seu sistema (não deleta na Evolution)
     */
    public function destroy($id)
    {
        $user = Auth::user();

        $instance = WhatsappInstance::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        $campaignCount = Campaign::where('whatsapp_instance_id', $instance->id)->count();
        if ($campaignCount > 0) {
            return redirect()->route('instancias.index')->with(
                'error',
                "Não é possível excluir esta instância porque existem {$campaignCount} campanha(s) vinculada(s) a ela."
            );
        }

        try {
            $instance->delete();
        } catch (QueryException $e) {
            if (($e->errorInfo[1] ?? null) == 1451) {
                return redirect()->route('instancias.index')->with(
                    'error',
                    'Não foi possível excluir esta instância porque existem registros vinculados a ela (ex: campanhas).'
                );
            }

            throw $e;
        }

        return redirect()->route('instancias.index')
            ->with('success', 'Instância removida do sistema.');
    }

    /**
     * ✅ GET settings da Evolution (Comportamento)
     * Agora usando EvolutionService e devolvendo JSON "normalizado" pra UI.
     */
    public function getSettings($id, EvolutionService $evo)
    {
        $user = Auth::user();

        $instance = WhatsappInstance::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        $resp = $evo->getSettings($instance->instance_name, $instance->token);

        if (!($resp['ok'] ?? false)) {
            Log::warning('Falha ao buscar settings na Evolution (service)', [
                'instance' => $instance->instance_name,
                'resp' => $resp,
            ]);

            return response()->json([
                'ok' => false,
                'message' => $resp['message'] ?? $resp['error'] ?? 'Não consegui buscar settings na Evolution.',
            ], 200);
        }

        // Normaliza a resposta (cada build retorna num caminho diferente)
        $raw = $resp['body'] ?? [];
        $settings =
            $raw['settings'] ??
            $raw['instance']['settings'] ??
            $raw['data']['settings'] ??
            $raw['body']['settings'] ??
            $raw;

        // Extrai campos comuns (fallbacks)
        $out = [
            'rejectCall'      => (bool)($settings['rejectCall'] ?? $settings['reject_call'] ?? $settings['rejectCalls'] ?? $settings['reject_calls'] ?? false),
            'msgCall'         => (string)($settings['msgCall'] ?? $settings['msg_call'] ?? $settings['messageCall'] ?? $settings['message_call'] ?? ''),
            'groupsIgnore'    => (bool)($settings['groupsIgnore'] ?? $settings['groups_ignore'] ?? $settings['ignoreGroups'] ?? $settings['ignore_groups'] ?? false),
            'alwaysOnline'    => (bool)($settings['alwaysOnline'] ?? $settings['always_online'] ?? false),
            'readMessages'    => (bool)($settings['readMessages'] ?? $settings['read_messages'] ?? false),
            'readStatus'      => (bool)($settings['readStatus'] ?? $settings['read_status'] ?? false),
            'syncFullHistory' => (bool)($settings['syncFullHistory'] ?? $settings['sync_full_history'] ?? false),
        ];

        return response()->json([
            'ok' => true,
            'settings' => $out,
            'raw' => $raw, // útil pra debug (se quiser remover depois, remove)
        ], 200);
    }

    /**
     * ✅ POST settings da Evolution (Comportamento)
     * Agora usando EvolutionService.
     */
    public function setSettings(Request $request, $id, EvolutionService $evo)
    {
        $user = Auth::user();

        $instance = WhatsappInstance::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        $payload = $request->validate([
            'rejectCall'      => ['nullable'],
            'msgCall'         => ['nullable', 'string', 'max:300'],
            'groupsIgnore'    => ['nullable'],
            'alwaysOnline'    => ['nullable'],
            'readMessages'    => ['nullable'],
            'readStatus'      => ['nullable'],
            'syncFullHistory' => ['nullable'],
        ]);

        // Normaliza booleans (checkbox pode vir "on"/"1"/true)
        $boolKeys = ['rejectCall','groupsIgnore','alwaysOnline','readMessages','readStatus','syncFullHistory'];
        foreach ($boolKeys as $k) {
            if (array_key_exists($k, $payload)) {
                $payload[$k] = filter_var($payload[$k], FILTER_VALIDATE_BOOLEAN);
            }
        }

        // Se msgCall não vier, garante string (evita null quebrar validação da API em algumas builds)
        if (!array_key_exists('msgCall', $payload)) {
            $payload['msgCall'] = '';
        }

        $resp = $evo->setSettings($instance->instance_name, $payload, $instance->token);

        if ($resp['ok'] ?? false) {
            return response()->json([
                'ok' => true,
                'message' => 'Configurações salvas com sucesso.',
                'response' => $resp['body'] ?? $resp,
            ], 200);
        }

        Log::warning('Falha ao salvar settings na Evolution (service)', [
            'instance' => $instance->instance_name,
            'resp' => $resp,
        ]);

        return response()->json([
            'ok' => false,
            'message' => $resp['message'] ?? $resp['error'] ?? 'Não consegui salvar as configurações na Evolution.',
        ], 200);
    }

    /**
     * Configura webhook na Evolution automaticamente
     */
    private function configureEvolutionWebhook(WhatsappInstance $instance): array
    {
        try {
            $base    = rtrim((string) config('evolution.base_url'), '/');
            $apiKey  = (string) config('evolution.api_key');
            $timeout = (int) config('evolution.timeout', 30);

            if ($base === '' || $apiKey === '') {
                return ['ok' => false, 'message' => 'Evolution base_url/api_key não configurados.'];
            }

            $secret = (string) env('EVOLUTION_WEBHOOK_SECRET', '');
            if ($secret === '') {
                return ['ok' => false, 'message' => 'EVOLUTION_WEBHOOK_SECRET não definido no .env.'];
            }

            $webhookUrl = (string) env('EVOLUTION_WEBHOOK_URL', '');
            if ($webhookUrl === '') {
                $appUrl = rtrim((string) config('app.url'), '/');
                if ($appUrl === '' || str_contains($appUrl, 'localhost') || str_contains($appUrl, '127.0.0.1')) {
                    return [
                        'ok' => false,
                        'message' => 'APP_URL não é público (localhost). Use EVOLUTION_WEBHOOK_URL apontando para um túnel (ngrok/cloudflared) ou produção.',
                    ];
                }
                $webhookUrl = $appUrl . '/webhooks/evolution';
            }

            if (!str_contains($webhookUrl, 'token=')) {
                $webhookUrl .= (str_contains($webhookUrl, '?') ? '&' : '?') . 'token=' . urlencode($secret);
            }

            $events = [
                'messages.upsert',
                'messages.update',
                'connection.update',
            ];

            $payloadFull = [
                'instance' => $instance->instance_name,
                'webhook' => [
                    'enabled' => true,
                    'url' => $webhookUrl,
                    'events' => $events,
                    'webhookByEvents' => false,
                    'webhookBase64' => false,
                ],
            ];

            $payloadAlt = [
                'instance' => $instance->instance_name,
                'enabled'  => true,
                'url'      => $webhookUrl,
                'events'   => $events,
            ];

            $endpoints = [
                ['POST', $base . '/webhook/set/' . urlencode($instance->instance_name), $payloadFull],
                ['POST', $base . '/webhook/set', $payloadFull],
                ['POST', $base . '/instance/webhook/' . urlencode($instance->instance_name), $payloadFull],
                ['POST', $base . '/instance/webhook', $payloadFull],
                ['POST', $base . '/webhook/set/' . urlencode($instance->instance_name), $payloadAlt],
                ['POST', $base . '/webhook/set', $payloadAlt],
            ];

            $tries = [];

            foreach ($endpoints as [$method, $url, $payload]) {
                try {
                    $resp = Http::timeout($timeout)
                        ->withHeaders([
                            'apikey'  => $apiKey,
                            'Accept'  => 'application/json',
                        ])
                        ->send($method, $url, ['json' => $payload]);

                    $tries[] = [
                        'url' => $url,
                        'status' => $resp->status(),
                        'ok' => $resp->successful(),
                        'snippet' => substr((string)$resp->body(), 0, 240),
                    ];

                    if ($resp->successful()) {
                        return [
                            'ok' => true,
                            'webhook_url' => $webhookUrl,
                            'endpoint' => $url,
                            'response' => $resp->json(),
                            'tries' => $tries,
                        ];
                    }
                } catch (\Throwable $e) {
                    $tries[] = [
                        'url' => $url,
                        'ok' => false,
                        'error' => $e->getMessage(),
                    ];
                }
            }

            return [
                'ok' => false,
                'message' => 'Não consegui configurar webhook: nenhum endpoint aceitou o payload.',
                'webhook_url' => $webhookUrl,
                'tries' => $tries,
            ];
        } catch (\Throwable $e) {
            return [
                'ok' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}
