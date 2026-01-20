<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\WhatsappInstanceController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\CampaignRecipientController;
use App\Http\Controllers\ChatController;

use App\Http\Controllers\Webhooks\EvolutionWebhookController;
use App\Services\EvolutionService;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('campanhas.index')
        : redirect()->route('login');
});

Auth::routes();

Route::get('/home', function () {
    return redirect()->route('campanhas.index');
})->name('home');

Route::post('/webhooks/evolution', [EvolutionWebhookController::class, 'handle'])
    ->name('webhooks.evolution')
    ->middleware('throttle:120,1');

Route::middleware('auth')->group(function () {

    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/users/{user}/instances', [DashboardController::class, 'userInstances'])->name('user.instances');
        Route::get('/instances/{instance}/events', [DashboardController::class, 'instanceEvents'])->name('instance.events');
    });

    // Instâncias
    Route::get('/instancias', [WhatsappInstanceController::class, 'index'])->name('instancias.index');
    Route::get('/instancias/nova', [WhatsappInstanceController::class, 'create'])->name('instancias.create');
    Route::post('/instancias', [WhatsappInstanceController::class, 'store'])->name('instancias.store');

    Route::get('/instancias/{id}/status', [WhatsappInstanceController::class, 'status'])->name('instancias.status');

    Route::get('/instancias/{id}/settings', [WhatsappInstanceController::class, 'getSettings'])->name('instancias.settings.get');
    Route::post('/instancias/{id}/settings', [WhatsappInstanceController::class, 'setSettings'])->name('instancias.settings.set');

    Route::post('/instancias/{id}/connect', [WhatsappInstanceController::class, 'connect'])->name('instancias.connect');
    Route::post('/instancias/{id}/disconnect', [WhatsappInstanceController::class, 'disconnect'])->name('instancias.disconnect');
    Route::post('/instancias/{id}/toggle', [WhatsappInstanceController::class, 'toggle'])->name('instancias.toggle');

    Route::delete('/instancias/{id}', [WhatsappInstanceController::class, 'destroy'])->name('instancias.destroy');

    // Campanhas
    Route::get('/campanhas', [CampaignController::class, 'index'])->name('campanhas.index');
    Route::get('/campanhas/nova', [CampaignController::class, 'create'])->name('campanhas.create');
    Route::post('/campanhas', [CampaignController::class, 'store'])->name('campanhas.store');

    Route::get('/campanhas/{id}', [CampaignController::class, 'show'])->name('campanhas.show');

    Route::post('/campanhas/{id}/destinatarios/importar', [CampaignController::class, 'importRecipients'])
        ->name('campanhas.recipients.import');

    Route::post('/campanhas/{id}/mensagens', [CampaignController::class, 'storeMessage'])
        ->name('campanhas.messages.store');

    Route::delete('/campanhas/{id}/mensagens/{messageId}', [CampaignController::class, 'destroyMessage'])
        ->name('campanhas.messages.destroy');

    // Disparo manual
    Route::post('/campanhas/{id}/disparar', [CampaignController::class, 'dispatchCampaign'])
        ->name('campanhas.dispatch');

    // Disparo automático + status (AJAX)
    Route::post('/campanhas/{id}/disparar/auto', [CampaignController::class, 'dispatchCampaignAuto'])
        ->name('campanhas.dispatch.auto');

    Route::get('/campanhas/{id}/disparar/status', [CampaignController::class, 'dispatchStatus'])
        ->name('campanhas.dispatch.status');

    // Destinatários
    Route::get('/campanhas/{campaign}/destinatarios/listar', [CampaignRecipientController::class, 'index'])
        ->name('campanhas.recipients');

    Route::delete('/campanhas/{campaign}/destinatarios/{recipient}/remover', [CampaignRecipientController::class, 'destroy'])
        ->name('campanhas.recipients.destroy');

    Route::post('/campanhas/{campaign}/destinatarios/dedup/rodar', [CampaignRecipientController::class, 'dedup'])
        ->name('campanhas.recipients.dedup');

    // Conversas
    Route::get('/conversas', [ChatController::class, 'index'])->name('chats.index');
    Route::get('/conversas/{chat}', [ChatController::class, 'show'])->name('chats.show');
    Route::post('/conversas/{chat}/mensagens', [ChatController::class, 'send'])->name('chats.send');
});

// Ping (public)
Route::get('/evolution/ping', function (EvolutionService $evo) {
    return response()->json($evo->ping());
});

if (app()->environment('local')) {

    Route::get('/debug-evo', function () {
        return response()->json([
            'base_url'     => config('evolution.base_url'),
            'api_key'      => config('evolution.api_key') ? 'OK (set)' : null,
            'timeout'      => config('evolution.timeout'),
            'env_base_url' => env('EVOLUTION_BASE_URL'),
        ]);
    });

    Route::get('/evolution/check', function () {
        $baseUrl = rtrim((string) config('evolution.base_url'), '/');

        if (!$baseUrl) {
            return response()->json([
                'error' => 'EVOLUTION_BASE_URL não configurado (config evolution.base_url está vazio).'
            ], 400);
        }

        $apiKey  = (string) config('evolution.api_key');
        $timeout = (int) config('evolution.timeout', 30);

        $paths = ['/', '/manager', '/manager/instances', '/instance', '/instances', '/swagger.json', '/openapi.json'];

        $out = [];

        foreach ($paths as $p) {
            $url = $baseUrl . $p;

            try {
                $resp = Http::timeout($timeout)
                    ->withHeaders(['apikey' => $apiKey, 'Accept' => 'application/json'])
                    ->get($url);

                $out[] = [
                    'path'         => $p,
                    'status'       => $resp->status(),
                    'content_type' => $resp->header('Content-Type'),
                    'snippet'      => substr($resp->body(), 0, 200),
                ];
            } catch (\Throwable $e) {
                $out[] = [
                    'path'  => $p,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return response()->json($out);
    });

    Route::get('/evolution/try-create', function (Request $request, EvolutionService $evo) {
        $name  = $request->query('name', 'teste_debug_' . rand(100, 999));
        $token = $request->query('token', 'tok_' . rand(1000, 9999));

        return response()->json($evo->createInstance($name, $token, true));
    });
}
