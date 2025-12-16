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
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ]);
    }

    private function clientWithInstanceToken(?string $instanceToken = null)
    {
        $client = $this->client();

        if ($instanceToken) {
            $client = $client->withHeaders([
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
                'ok' => $resp->successful(),
                'tested_url' => $this->baseUrl . '/',
                'http_status' => $resp->status(),
                'body' => $this->safeJson($resp->body()),
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
                        'ok' => true,
                        'url' => $url,
                        'integration_used' => $integration,
                        'http_status' => $resp->status(),
                        'body' => $body,
                        'tries' => $tries,
                    ];
                }

                $msg = $this->extractMessage($body);

                if ($msg && stripos($msg, 'Invalid integration') === false) {
                    return [
                        'ok' => false,
                        'url' => $url,
                        'integration_used' => $integration,
                        'http_status' => $resp->status(),
                        'message' => $msg,
                        'body' => $body,
                        'tries' => $tries,
                    ];
                }

            } catch (\Throwable $e) {
                $attempt = [
                    'integration' => $integration,
                    'error' => $e->getMessage(),
                ];
                $tries[] = $attempt;
                $last    = $attempt;
            }
        }

        return [
            'ok' => false,
            'url' => $url,
            'error' => 'Não consegui criar instância: nenhuma integração foi aceita.',
            'last_try' => $last,
            'tries' => $tries,
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

            // alguns ambientes expõem qrcode separado:
            ['GET',  '/instance/qrcode/' . urlencode($instanceName), null],
            ['POST', '/instance/qrcode', ['instanceName' => $instanceName]],
        ];

        foreach ($candidates as [$method, $path, $payload]) {
            $url = $this->baseUrl . $path;

            try {
                $resp = $payload
                    ? $this->clientWithInstanceToken($instanceToken)->post($url, $payload)
                    : $this->clientWithInstanceToken($instanceToken)->get($url);

                $body = $this->safeJson($resp->body());

                $attempt = [
                    'url' => $url,
                    'method' => $method,
                    'payload' => $payload,
                    'http_status' => $resp->status(),
                    'body' => $body,
                ];

                $tries[] = $attempt;
                $last    = $attempt;

                if ($resp->successful()) {
                    $qr = $this->extractQr($body);

                    return [
                        'ok' => true,
                        'url' => $url,
                        'method' => $method,
                        'http_status' => $resp->status(),
                        'body' => $body,
                        'qr_base64' => $qr['base64'],
                        'qr_data_uri' => $qr['data_uri'],
                        'tries' => $tries,
                    ];
                }

                if ($resp->status() !== 404) {
                    return [
                        'ok' => false,
                        'url' => $url,
                        'method' => $method,
                        'http_status' => $resp->status(),
                        'message' => $this->extractMessage($body),
                        'body' => $body,
                        'tries' => $tries,
                    ];
                }

            } catch (\Throwable $e) {
                $attempt = [
                    'url' => $url,
                    'method' => $method,
                    'payload' => $payload,
                    'error' => $e->getMessage(),
                ];
                $tries[] = $attempt;
                $last = $attempt;
            }
        }

        return [
            'ok' => false,
            'error' => 'Não consegui conectar/gerar QR: nenhum endpoint aceito.',
            'last_try' => $last,
            'tries' => $tries,
        ];
    }

    /**
     * sendText($instance, $to, $msg, $token)
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
                        'url' => $url,
                        'payload' => $payload,
                        'http_status' => $resp->status(),
                        'body' => $body,
                    ];

                    $tries[] = $attempt;
                    $last = $attempt;

                    if ($resp->successful()) {
                        return [
                            'ok' => true,
                            'url' => $url,
                            'http_status' => $resp->status(),
                            'body' => $body,
                            'tries' => $tries,
                        ];
                    }

                    if ($resp->status() !== 404) {
                        return [
                            'ok' => false,
                            'url' => $url,
                            'http_status' => $resp->status(),
                            'message' => $this->extractMessage($body),
                            'body' => $body,
                            'tries' => $tries,
                        ];
                    }
                } catch (\Throwable $e) {
                    $attempt = [
                        'url' => $url,
                        'payload' => $payload,
                        'error' => $e->getMessage(),
                    ];
                    $tries[] = $attempt;
                    $last = $attempt;
                }
            }
        }

        return [
            'ok' => false,
            'error' => 'Não consegui enviar: nenhum endpoint de sendText foi aceito.',
            'last_try' => $last,
            'tries' => $tries,
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

            // algumas builds devolvem image já como data-uri dentro de "code" ou "image"
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
            'base64' => $base64,
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

    private function safeJson(string $body)
    {
        $decoded = json_decode($body, true);
        return json_last_error() === JSON_ERROR_NONE ? $decoded : $body;
    }
}
