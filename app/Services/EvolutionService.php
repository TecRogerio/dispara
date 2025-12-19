<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class EvolutionService
{
    private string $baseUrl;
    private string $apiKey;
    private int $timeout;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('evolution.base_url'), '/');
        $this->apiKey  = (string) config('evolution.api_key');
        $this->timeout = (int) config('evolution.timeout', 30);
    }

    private function client()
    {
        return Http::timeout($this->timeout)
            ->withHeaders([
                'apikey'       => $this->apiKey,
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ]);
    }

    private function clientWithInstanceToken(?string $instanceToken = null)
    {
        $client = $this->client();

        // Algumas builds da Evolution aceitam token por header (varia)
        if (is_string($instanceToken) && $instanceToken !== '') {
            $client = $client->withHeaders([
                // mantém compatibilidade com ambientes variados
                'Authorization'    => 'Bearer ' . $instanceToken,
                'x-instance-token' => $instanceToken,
                'instanceToken'    => $instanceToken,
                'token'            => $instanceToken,
            ]);
        }

        return $client;
    }

    public function ping(): array
    {
        try {
            $resp = $this->client()->get($this->baseUrl . '/');

            return [
                'ok'         => $resp->successful(),
                'tested_url' => $this->baseUrl . '/',
                'http_status'=> $resp->status(),
                'body'       => $this->safeJson($resp->body()),
            ];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => $e->getMessage()];
        }
    }

    public function createInstance(string $instanceName, string $token, bool $qrcode = true): array
    {
        $url = $this->baseUrl . '/instance/create';

        $integrationCandidates = array_values(array_filter([
            config('evolution.integration'),
            'WHATSAPP-BAILEYS',
            'BAILEYS',
            'EVOLUTION',
            'WHATSAPP',
            'WHATSAPP-WEB',
        ]));

        $tries = [];
        $last  = null;

        foreach ($integrationCandidates as $integration) {

            $payload = [
                'instanceName'  => $instanceName,
                'token'         => $token,
                'instanceToken' => $token,
                'qrcode'        => $qrcode,
                'integration'   => $integration,
            ];

            try {
                $resp = $this->client()->post($url, $payload);
                $body = $this->safeJson($resp->body());

                $attempt = [
                    'integration' => $integration,
                    'http_status' => $resp->status(),
                    'body'        => $body,
                ];

                $tries[] = $attempt;
                $last    = $attempt;

                if ($resp->successful()) {
                    return [
                        'ok'               => true,
                        'url'              => $url,
                        'integration_used' => $integration,
                        'http_status'      => $resp->status(),
                        'body'             => $body,
                        'tries'            => $tries,
                    ];
                }

                $msg = $this->extractMessage($body);

                // se não é erro de integração, devolve logo o erro
                if ($msg && stripos($msg, 'Invalid integration') === false) {
                    return [
                        'ok'               => false,
                        'url'              => $url,
                        'integration_used' => $integration,
                        'http_status'      => $resp->status(),
                        'message'          => $msg,
                        'body'             => $body,
                        'tries'            => $tries,
                    ];
                }

            } catch (\Throwable $e) {
                $attempt = [
                    'integration' => $integration,
                    'error'       => $e->getMessage(),
                ];
                $tries[] = $attempt;
                $last    = $attempt;
            }
        }

        return [
            'ok'       => false,
            'url'      => $url,
            'error'    => 'Não consegui criar instância: nenhuma integração foi aceita.',
            'last_try' => $last,
            'tries'    => $tries,
        ];
    }

    /**
     * connect($instance, $token)
     * - Se já estiver conectada, normalmente vem: body.instance.state = "open"
     * - Se precisar parear, algumas builds devolvem base64 em body.base64
     */
    public function connect(string $instanceName, ?string $instanceToken = null): array
    {
        $tries = [];
        $last  = null;

        $candidates = [
            ['GET',  '/instance/connect/' . urlencode($instanceName), null],
            ['POST', '/instance/connect', ['instanceName' => $instanceName]],

            ['GET',  '/instance/qrcode/' . urlencode($instanceName), null],
            ['POST', '/instance/qrcode', ['instanceName' => $instanceName]],
        ];

        foreach ($candidates as [$method, $path, $payload]) {
            $url = $this->baseUrl . $path;

            try {
                $method = strtolower((string) $method);

                if ($method === 'post') {
                    $resp = $this->clientWithInstanceToken($instanceToken)->post($url, $payload ?? []);
                } else {
                    $resp = $this->clientWithInstanceToken($instanceToken)->get($url);
                }

                $body = $this->safeJson($resp->body());

                $attempt = [
                    'url'        => $url,
                    'method'     => strtoupper($method),
                    'payload'    => $payload,
                    'http_status'=> $resp->status(),
                    'body'       => $body,
                ];

                $tries[] = $attempt;
                $last    = $attempt;

                if ($resp->successful()) {
                    $qr = $this->extractQr($body);

                    return [
                        'ok'          => true,
                        'url'         => $url,
                        'method'      => strtoupper($method),
                        'http_status' => $resp->status(),
                        'body'        => $body,
                        'qr_base64'   => $qr['base64'],
                        'qr_data_uri' => $qr['data_uri'],
                        'tries'       => $tries,
                    ];
                }

                if ($resp->status() !== 404) {
                    return [
                        'ok'          => false,
                        'url'         => $url,
                        'method'      => strtoupper($method),
                        'http_status' => $resp->status(),
                        'message'     => $this->extractMessage($body),
                        'body'        => $body,
                        'tries'       => $tries,
                    ];
                }

            } catch (\Throwable $e) {
                $attempt = [
                    'url'     => $url,
                    'method'  => strtoupper((string) $method),
                    'payload' => $payload,
                    'error'   => $e->getMessage(),
                ];
                $tries[] = $attempt;
                $last = $attempt;
            }
        }

        return [
            'ok'       => false,
            'error'    => 'Não consegui conectar/gerar QR: nenhum endpoint aceito.',
            'last_try' => $last,
            'tries'    => $tries,
        ];
    }

    /**
     * ✅ disconnectInstance($instance, $token)
     * - Faz logout/desconexão da sessão WhatsApp daquela instância
     * - Fallback de endpoints pois varia por versão/build
     */
    public function disconnectInstance(string $instanceName, ?string $instanceToken = null): array
    {
        $tries = [];
        $last  = null;

        // endpoints candidatos (varia por versão)
        $candidates = [
            ['DELETE', '/instance/logout/' . urlencode($instanceName), null],
            ['DELETE', '/instance/logout-instance/' . urlencode($instanceName), null],
            ['DELETE', '/instance/logoutInstance/' . urlencode($instanceName), null],
            ['DELETE', '/instance/disconnect/' . urlencode($instanceName), null],
            ['POST',   '/instance/logout', ['instanceName' => $instanceName]],
            ['POST',   '/instance/disconnect', ['instanceName' => $instanceName]],
            ['DELETE', '/instance/logout', ['instanceName' => $instanceName]],
            ['DELETE', '/instance/disconnect', ['instanceName' => $instanceName]],
        ];

        foreach ($candidates as [$method, $path, $payloadOrQuery]) {
            $url = $this->baseUrl . $path;

            try {
                $m = strtolower($method);

                $client = $this->clientWithInstanceToken($instanceToken);

                if ($m === 'post') {
                    $resp = $client->post($url, $payloadOrQuery ?? []);
                } elseif ($m === 'delete') {
                    // Algumas APIs aceitam query na URL em DELETE
                    if (is_array($payloadOrQuery) && !empty($payloadOrQuery)) {
                        $resp = $client->send('DELETE', $url, ['query' => $payloadOrQuery]);
                    } else {
                        $resp = $client->send('DELETE', $url);
                    }
                } else {
                    $resp = $client->send(strtoupper($method), $url, is_array($payloadOrQuery) ? ['json' => $payloadOrQuery] : []);
                }

                $body = $this->safeJson($resp->body());

                $attempt = [
                    'url'        => $url,
                    'method'     => strtoupper($method),
                    'payload'    => $payloadOrQuery,
                    'http_status'=> $resp->status(),
                    'body'       => $body,
                ];

                $tries[] = $attempt;
                $last    = $attempt;

                if ($resp->successful()) {
                    return [
                        'ok'          => true,
                        'url'         => $url,
                        'method'      => strtoupper($method),
                        'http_status' => $resp->status(),
                        'body'        => $body,
                        'tries'       => $tries,
                    ];
                }

                // se não for 404, devolve erro imediatamente
                if ($resp->status() !== 404) {
                    return [
                        'ok'          => false,
                        'url'         => $url,
                        'method'      => strtoupper($method),
                        'http_status' => $resp->status(),
                        'message'     => $this->extractMessage($body) ?? 'Falha ao desconectar.',
                        'body'        => $body,
                        'tries'       => $tries,
                    ];
                }

            } catch (\Throwable $e) {
                $attempt = [
                    'url'     => $url,
                    'method'  => strtoupper($method),
                    'payload' => $payloadOrQuery,
                    'error'   => $e->getMessage(),
                ];
                $tries[] = $attempt;
                $last    = $attempt;
            }
        }

        return [
            'ok'       => false,
            'error'    => 'Não consegui desconectar: nenhum endpoint de logout/disconnect foi aceito.',
            'last_try' => $last,
            'tries'    => $tries,
        ];
    }

    /**
     * ✅ getSettings($instance, $token)
     * Busca settings / comportamento da instância
     */
    public function getSettings(string $instanceName, ?string $instanceToken = null): array
    {
        $tries = [];
        $last  = null;

        $candidates = [
            ['GET', '/settings/get/' . urlencode($instanceName), null],
            ['GET', '/settings/' . urlencode($instanceName), null],
            ['GET', '/instance/settings/' . urlencode($instanceName), null],

            ['GET', '/settings/get', ['instanceName' => $instanceName]],
            ['GET', '/settings', ['instanceName' => $instanceName]],
            ['GET', '/instance/settings', ['instanceName' => $instanceName]],
        ];

        foreach ($candidates as [$method, $path, $query]) {
            $url = $this->baseUrl . $path;

            try {
                $client = $this->clientWithInstanceToken($instanceToken);

                if (is_array($query) && !empty($query)) {
                    $resp = $client->get($url, $query);
                } else {
                    $resp = $client->get($url);
                }

                $body = $this->safeJson($resp->body());

                $attempt = [
                    'url'        => $url,
                    'method'     => $method,
                    'query'      => $query,
                    'http_status'=> $resp->status(),
                    'body'       => $body,
                ];

                $tries[] = $attempt;
                $last    = $attempt;

                if ($resp->successful()) {
                    return [
                        'ok'          => true,
                        'url'         => $url,
                        'method'      => $method,
                        'http_status' => $resp->status(),
                        'body'        => $body,
                        'tries'       => $tries,
                    ];
                }

                if ($resp->status() !== 404) {
                    return [
                        'ok'          => false,
                        'url'         => $url,
                        'method'      => $method,
                        'http_status' => $resp->status(),
                        'message'     => $this->extractMessage($body) ?? 'Falha ao buscar settings.',
                        'body'        => $body,
                        'tries'       => $tries,
                    ];
                }

            } catch (\Throwable $e) {
                $attempt = [
                    'url'    => $url,
                    'method' => $method,
                    'query'  => $query,
                    'error'  => $e->getMessage(),
                ];
                $tries[] = $attempt;
                $last    = $attempt;
            }
        }

        return [
            'ok'       => false,
            'error'    => 'Não consegui buscar settings: nenhum endpoint aceito.',
            'last_try' => $last,
            'tries'    => $tries,
        ];
    }

    /**
     * ✅ setSettings($instance, $settings, $token)
     * Salva settings / comportamento
     */
    public function setSettings(string $instanceName, array $settings, ?string $instanceToken = null): array
    {
        $tries = [];
        $last  = null;

        // payloads possíveis (varia por build)
        $payloads = [
            array_merge(['instanceName' => $instanceName], $settings),
            ['instanceName' => $instanceName, 'settings' => $settings],
            ['instance' => $instanceName, 'settings' => $settings],
            array_merge(['instance' => $instanceName], $settings),
        ];

        $endpoints = [
            ['POST', '/settings/set/' . urlencode($instanceName)],
            ['POST', '/settings/set'],
            ['POST', '/instance/settings/' . urlencode($instanceName)],
            ['POST', '/instance/settings'],
            ['PUT',  '/instance/settings/' . urlencode($instanceName)],
            ['PUT',  '/settings/set'],
        ];

        foreach ($endpoints as [$method, $path]) {
            $url = $this->baseUrl . $path;

            foreach ($payloads as $payload) {
                try {
                    $m = strtolower($method);

                    if ($m === 'put') {
                        $resp = $this->clientWithInstanceToken($instanceToken)->put($url, $payload);
                    } else {
                        $resp = $this->clientWithInstanceToken($instanceToken)->post($url, $payload);
                    }

                    $body = $this->safeJson($resp->body());

                    $attempt = [
                        'url'        => $url,
                        'method'     => $method,
                        'payload'    => $payload,
                        'http_status'=> $resp->status(),
                        'body'       => $body,
                    ];

                    $tries[] = $attempt;
                    $last    = $attempt;

                    if ($resp->successful()) {
                        return [
                            'ok'          => true,
                            'url'         => $url,
                            'method'      => $method,
                            'http_status' => $resp->status(),
                            'body'        => $body,
                            'tries'       => $tries,
                        ];
                    }

                    if ($resp->status() !== 404) {
                        return [
                            'ok'          => false,
                            'url'         => $url,
                            'method'      => $method,
                            'http_status' => $resp->status(),
                            'message'     => $this->extractMessage($body) ?? 'Falha ao salvar settings.',
                            'body'        => $body,
                            'tries'       => $tries,
                        ];
                    }

                } catch (\Throwable $e) {
                    $attempt = [
                        'url'     => $url,
                        'method'  => $method,
                        'payload' => $payload,
                        'error'   => $e->getMessage(),
                    ];
                    $tries[] = $attempt;
                    $last    = $attempt;
                }
            }
        }

        return [
            'ok'       => false,
            'error'    => 'Não consegui salvar settings: nenhum endpoint aceito.',
            'last_try' => $last,
            'tries'    => $tries,
        ];
    }

    /**
     * setWebhook(...)
     */
    public function setWebhook(
        string $instanceName,
        string $webhookUrl,
        array $events = ['messages.upsert', 'messages.update', 'connection.update'],
        bool $byEvents = false,
        bool $base64 = false,
        ?string $instanceToken = null
    ): array {
        $tries = [];
        $last  = null;

        $webhookUrl = trim($webhookUrl);

        $payloads = [
            [
                'instanceName' => $instanceName,
                'webhook' => [
                    'enabled'  => true,
                    'url'      => $webhookUrl,
                    'events'   => array_values($events),
                    'byEvents' => $byEvents,
                    'base64'   => $base64,
                ],
            ],
            [
                'instanceName' => $instanceName,
                'url'          => $webhookUrl,
                'events'       => array_values($events),
                'byEvents'     => $byEvents,
                'base64'       => $base64,
                'enabled'      => true,
            ],
            [
                'instance' => $instanceName,
                'webhook'  => $webhookUrl,
                'events'   => array_values($events),
            ],
        ];

        $endpoints = [
            ['POST', '/webhook/set'],
            ['POST', '/webhook/set/' . urlencode($instanceName)],
            ['POST', '/webhook/instance/' . urlencode($instanceName)],
            ['POST', '/instance/webhook/' . urlencode($instanceName)],
            ['POST', '/instance/webhook'],
            ['PUT',  '/instance/webhook/' . urlencode($instanceName)],
        ];

        foreach ($endpoints as [$method, $path]) {
            $url = $this->baseUrl . $path;

            foreach ($payloads as $payload) {
                try {
                    $m = strtolower($method);

                    if ($m === 'put') {
                        $resp = $this->clientWithInstanceToken($instanceToken)->put($url, $payload);
                    } else {
                        $resp = $this->clientWithInstanceToken($instanceToken)->post($url, $payload);
                    }

                    $body = $this->safeJson($resp->body());

                    $attempt = [
                        'url'        => $url,
                        'method'     => $method,
                        'payload'    => $payload,
                        'http_status'=> $resp->status(),
                        'body'       => $body,
                    ];

                    $tries[] = $attempt;
                    $last    = $attempt;

                    if ($resp->successful()) {
                        return [
                            'ok'          => true,
                            'url'         => $url,
                            'method'      => $method,
                            'http_status' => $resp->status(),
                            'body'        => $body,
                            'tries'       => $tries,
                        ];
                    }

                    if ($resp->status() !== 404) {
                        return [
                            'ok'          => false,
                            'url'         => $url,
                            'method'      => $method,
                            'http_status' => $resp->status(),
                            'message'     => $this->extractMessage($body) ?? 'Falha ao setar webhook.',
                            'body'        => $body,
                            'tries'       => $tries,
                        ];
                    }

                } catch (\Throwable $e) {
                    $attempt = [
                        'url'     => $url,
                        'method'  => $method,
                        'payload' => $payload,
                        'error'   => $e->getMessage(),
                    ];
                    $tries[] = $attempt;
                    $last = $attempt;
                }
            }
        }

        return [
            'ok'       => false,
            'error'    => 'Não consegui configurar webhook: nenhum endpoint aceito.',
            'last_try' => $last,
            'tries'    => $tries,
        ];
    }

    /**
     * sendText(...)
     */
    public function sendText(string $instanceName, string $toDigits, string $message, ?string $instanceToken = null): array
    {
        $tries = [];
        $last  = null;

        $candidates = [
            ['/message/sendText/' . urlencode($instanceName), 'post'],
            ['/message/sendText', 'post'],
            ['/messages/sendText/' . urlencode($instanceName), 'post'],
            ['/messages/sendText', 'post'],
            ['/message/send-text/' . urlencode($instanceName), 'post'],
            ['/sendText/' . urlencode($instanceName), 'post'],
        ];

        $payloads = [
            ['number' => $toDigits, 'text' => $message],
            ['to' => $toDigits, 'text' => $message],
            ['to' => $toDigits, 'message' => $message],
            ['number' => $toDigits, 'message' => $message],
            ['instanceName' => $instanceName, 'number' => $toDigits, 'text' => $message],
            ['instanceName' => $instanceName, 'to' => $toDigits, 'message' => $message],
        ];

        foreach ($candidates as [$path, $method]) {
            $url = $this->baseUrl . $path;

            foreach ($payloads as $payload) {
                try {
                    $resp = $this->clientWithInstanceToken($instanceToken)->{$method}($url, $payload);
                    $body = $this->safeJson($resp->body());

                    $attempt = [
                        'url'        => $url,
                        'payload'    => $payload,
                        'http_status'=> $resp->status(),
                        'body'       => $body,
                    ];

                    $tries[] = $attempt;
                    $last = $attempt;

                    if ($resp->successful()) {
                        return [
                            'ok'          => true,
                            'url'         => $url,
                            'http_status' => $resp->status(),
                            'body'        => $body,
                            'tries'       => $tries,
                        ];
                    }

                    if ($resp->status() !== 404) {
                        return [
                            'ok'          => false,
                            'url'         => $url,
                            'http_status' => $resp->status(),
                            'message'     => $this->extractMessage($body),
                            'body'        => $body,
                            'tries'       => $tries,
                        ];
                    }
                } catch (\Throwable $e) {
                    $attempt = [
                        'url'     => $url,
                        'payload' => $payload,
                        'error'   => $e->getMessage(),
                    ];
                    $tries[] = $attempt;
                    $last = $attempt;
                }
            }
        }

        return [
            'ok'       => false,
            'error'    => 'Não consegui enviar: nenhum endpoint de sendText foi aceito.',
            'last_try' => $last,
            'tries'    => $tries,
        ];
    }

    private function extractQr($body): array
    {
        $base64 = null;

        if (is_array($body)) {
            $base64 = $body['base64'] ?? null;

            if (!$base64 && isset($body['qrcode']) && is_array($body['qrcode'])) {
                $base64 = $body['qrcode']['base64'] ?? null;
            }

            if (!$base64 && isset($body['qrcode']) && is_string($body['qrcode'])) {
                $base64 = $body['qrcode'];
            }

            if (!$base64 && isset($body['data']) && is_array($body['data'])) {
                $base64 = $body['data']['base64'] ?? ($body['data']['image'] ?? null);
            }

            if (!$base64 && isset($body['code']) && is_string($body['code'])) {
                $base64 = $body['code'];
            }
        }

        $dataUri = null;

        if (is_string($base64) && $base64 !== '') {
            if (stripos($base64, 'data:image') === 0) {
                $dataUri = $base64;
                $parts = explode(',', $base64, 2);
                if (count($parts) === 2) $base64 = $parts[1];
            } else {
                $dataUri = 'data:image/png;base64,' . $base64;
            }
        } else {
            $base64 = null;
        }

        return [
            'base64'   => $base64,
            'data_uri' => $dataUri,
        ];
    }

    private function extractMessage($body): ?string
    {
        if (!is_array($body)) return null;

        $msg = $body['response']['message'][0] ?? null;
        if (is_string($msg) && $msg !== '') return $msg;

        $msg = $body['message'] ?? null;
        if (is_string($msg) && $msg !== '') return $msg;

        $msg = $body['error'] ?? null;
        if (is_string($msg) && $msg !== '') return $msg;

        return null;
    }

    private function safeJson($body)
    {
        if (is_array($body)) return $body;

        if (!is_string($body)) return $body;

        $decoded = json_decode($body, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $body;
    }
}
