<?php

namespace App\Http\Controllers;

use App\Models\Chat;
use App\Models\Message;
use App\Models\WhatsappInstance;
use App\Services\EvolutionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();
        $q = trim((string) $request->get('q', ''));

        $chats = Chat::query()
            ->where('user_id', $userId)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('remote_jid', 'like', "%{$q}%")
                       ->orWhere('title', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->paginate(20)
            ->withQueryString();

        return view('chats.index', compact('chats', 'q'));
    }

    public function show(Request $request, Chat $chat)
    {
        abort_unless((int) $chat->user_id === (int) Auth::id(), 403);

        // Sidebar: lista de chats (para o blade não quebrar com $chats)
        $chats = Chat::query()
            ->where('user_id', Auth::id())
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->limit(50)
            ->get();

        // Mensagens paginadas (DESC para performance)
        $messages = Message::query()
            ->where('chat_id', $chat->id)
            ->orderByDesc('message_at')
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        /**
         * Mensagens ASC para a view (corrige "Undefined variable $messagesAsc")
         * Pega as 50 últimas, mas entrega em ordem ASC (do mais antigo pro mais novo)
         */
        $messagesAsc = Message::query()
            ->where('chat_id', $chat->id)
            ->orderByDesc('message_at')
            ->orderByDesc('id')
            ->limit(50)
            ->get()
            ->reverse()
            ->values();

        return view('chats.show', compact('chat', 'chats', 'messages', 'messagesAsc'));
    }

    public function send(Request $request, Chat $chat, EvolutionService $evo)
    {
        abort_unless((int) $chat->user_id === (int) Auth::id(), 403);

        $data = $request->validate([
            'text' => ['required', 'string', 'min:1', 'max:4000'],
        ]);

        $text = trim($data['text']);

        // 1) Instância do chat, senão pega a última habilitada do usuário
        $instance = WhatsappInstance::query()
            ->where('id', $chat->whatsapp_instance_id)
            ->where('user_id', Auth::id())
            ->first();

        if (!$instance) {
            $instance = WhatsappInstance::query()
                ->where('user_id', Auth::id())
                ->where('enabled', 1) // <- importante (no teu projeto é enabled)
                ->orderByDesc('id')
                ->first();
        }

        if (!$instance) {
            return back()->with('error', 'Nenhuma instância habilitada encontrada para enviar mensagens.');
        }

        // 2) Envia via EvolutionService
        $toDigits = $this->jidToDigits($chat->remote_jid) ?? $chat->remote_jid;

        $resp = $evo->sendText($instance->instance_name, (string) $toDigits, $text, $instance->token);

        $sentOk = (bool) ($resp['ok'] ?? false);

        // 3) Salva no banco (mesmo se falhar, pra log/UX)
        try {
            Message::create([
                'chat_id'             => $chat->id,
                'contact_id'          => null,
                'provider_message_id' => data_get($resp, 'body.key.id') ?: data_get($resp, 'body.id') ?: null,
                'direction'           => 'outbound',
                'type'                => 'text',
                'body'                => $text,
                'status'              => $sentOk ? 'sent' : 'failed',
                'message_at'          => now(),
                'raw'                 => $resp,
            ]);

            $chat->last_message_at = now();
            $chat->save();
        } catch (\Throwable $e) {
            Log::warning('Falha ao salvar message outbound', ['err' => $e->getMessage()]);
        }

        return redirect()
            ->route('chats.show', $chat->id)
            ->with(
                $sentOk ? 'success' : 'error',
                $sentOk ? 'Mensagem enviada.' : ('Falha ao enviar pela Evolution: ' . ($resp['message'] ?? $resp['error'] ?? 'verifique endpoints/instância'))
            );
    }

    /**
     * Sync manual (somente saúde da Evolution / não importa histórico)
     */
    public function sync(Request $request, EvolutionService $evo)
    {
        $userId = Auth::id();

        $instance = WhatsappInstance::query()
            ->where('user_id', $userId)
            ->where('enabled', 1)
            ->orderByDesc('id')
            ->first();

        if (!$instance) {
            return back()->with('error', 'Você não tem instância habilitada para sincronizar.');
        }

        $ping = $evo->ping();

        if (!($ping['ok'] ?? false)) {
            return back()->with('error', 'Evolution não respondeu OK no ping: ' . ($ping['error'] ?? ''));
        }

        $instance->touch();

        return back()->with(
            'success',
            'Sync OK: Evolution respondeu. As mensagens entram em tempo real via Webhook (histórico não é importado automaticamente).'
        );
    }

    private function jidToDigits(?string $jid): ?string
    {
        if (!is_string($jid) || trim($jid) === '') {
            return null;
        }

        $base = explode('@', $jid)[0] ?? '';
        $digits = preg_replace('/\D+/', '', $base);
        $digits = is_string($digits) ? $digits : '';

        return $digits !== '' ? $digits : null;
    }
}
