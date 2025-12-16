<?php

return [
    'base_url' => rtrim(env('EVOLUTION_BASE_URL', ''), '/'),
    'api_key'  => env('EVOLUTION_API_KEY', ''),
    'instance' => env('EVOLUTION_INSTANCE', ''),
    'timeout'  => (int) env('EVOLUTION_TIMEOUT', 30),
];
