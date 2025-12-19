<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Evolution API (base)
    |--------------------------------------------------------------------------
    |
    | Ex:
    | EVOLUTION_BASE_URL=https://evolutionapi.agendeizap.com.br
    | EVOLUTION_API_KEY=xxxx
    |
    */
    'base_url' => rtrim(trim((string) env('EVOLUTION_BASE_URL', '')), '/'),
    'api_key'  => (string) env('EVOLUTION_API_KEY', ''),
    'timeout'  => (int) env('EVOLUTION_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Instance (opcional)
    |--------------------------------------------------------------------------
    |
    | Para projetos de instância única você poderia usar isso,
    | mas no teu caso (multi instâncias) geralmente fica vazio.
    |
    */
    'instance' => (string) env('EVOLUTION_INSTANCE', ''),

    /*
    |--------------------------------------------------------------------------
    | Webhook (Evolution -> CRM)
    |--------------------------------------------------------------------------
    |
    | Importante: o webhook precisa ser uma URL PUBLICAMENTE acessível
    | (produção ou túnel).
    |
    | EVOLUTION_WEBHOOK_URL (opcional):
    |   Se não definir, o sistema pode montar via APP_URL + /webhooks/evolution
    |
    | EVOLUTION_WEBHOOK_SECRET (obrigatório):
    |   Token/secret para validar chamadas do webhook.
    |
    */
    'webhook' => [
        'url'    => (string) env('EVOLUTION_WEBHOOK_URL', ''),
        'secret' => (string) env('EVOLUTION_WEBHOOK_SECRET', ''),
        'events' => array_values(array_filter(array_map('trim', explode(',', (string) env(
            'EVOLUTION_WEBHOOK_EVENTS',
            'messages.upsert,messages.update,connection.update'
        ))))),
        'by_events' => (bool) env('EVOLUTION_WEBHOOK_BY_EVENTS', false),
        'base64'    => (bool) env('EVOLUTION_WEBHOOK_BASE64', false),
    ],

];
