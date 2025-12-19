<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Webhooks\EvolutionWebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Rotas da API do backend.
| Tudo aqui já nasce com o prefixo /api (RouteServiceProvider).
|
*/

// ----------------------------------------------------------
// Healthcheck simples
// ----------------------------------------------------------
Route::get('/ping', function () {
    return response()->json(['status' => 'ok']);
});

// ----------------------------------------------------------
// Webhook da Evolution (entrada de mensagens/eventos)
// URL final: /api/webhooks/evolution
// ----------------------------------------------------------
Route::post('/webhooks/evolution', [EvolutionWebhookController::class, 'handle'])
    ->name('webhooks.evolution');

// ----------------------------------------------------------
// Exemplo padrão (Sanctum) - mantém para compatibilidade
// ----------------------------------------------------------
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
