<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use App\Models\Contact;
use App\Models\Message;
use App\Models\WhatsappInstance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EvolutionWebhookController extends Controller
{
    public function handle(Request $request)
    {
        // 1) Validação do SECRET (aceita header ou query token)
        $secret = (string) env('EVOLUTION_WEBHOOK_SECRET', '');
        if ($secret === '') {
            return response()->json(['error' => 'EVOLUTION_WEBHOOK_SECRET not configured'], 500);
        }

        $provided =
            (string) $request->query('token', '') ?:
            (string) $request->header('X-Webhook-Token', '') ?:
            (string) $request->header('x-webhook-token', '') ?:
            (string) $request->header('x-evolution-secret', '') ?:
            (string) $request->header('x-webhook-secret', '') ?:
            (string) $request->header('x-agendeizap-secret', '') ?:
            (string) $request->header('Authorization', '');

        // Normaliza "Bearer xxx"
        if (is_string($provided) && stripos($provided, 'bearer ') === 0) {
            $provided = trim(substr($provided, 7));
        }

        if ($provided === '' || !hash_equals($secret, (string) $provided)) {
            Log::warning('Evolution webhook: unauthorized', [
                'ip' => $request->ip(),
                'ua' => $request->userAgent(),
            ]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // 2) Payload
        $payload = $request->all();

        // 2.1) Log leve (ajuda debug sem poluir muito)
        Log::info('Evolution webhook recebido', [
            'event' => $this->guessEvent($payload),
            'instance_guess' => $this->guessInstanceName($payload),
        ]);

        // 3) Filtra evento (processa apenas eventos de mensagem)
        $event = $this->guessEvent($payload);
        if ($event && !str_contains($event, 'message')) {
            return response()->json(['ok' => true, 'ignored' => 'non_message_event'], 200);
        }

        // 4) Descobrir instance_name
        $instanceName = $this->guessInstanceName($payload);

        if (!$instanceName) {
            Log::warning('Evolution webhook: instance_name ausente', [
                'keys' => array_slice(array_keys($payload), 0, 30),
            ]);
            return response()->json(['ok' => true, 'ignored' => 'missing_instance'], 202);
        }

        /** @var WhatsappInstance|null $instance */
        $instance = WhatsappInstance::query()
            ->where('instance_name', $instanceName)
            ->first();

        if (!$instance) {
            Log::warning('Evolution webhook: instância não encontrada no banco', ['instance' => $instanceName]);
            return response()->json(['ok' => true, 'ignored' => 'unknown_instance'], 202);
        }

        // 5) Extrair dados da mensagem (tolerante)
        $remoteJid =
            data_get($payload, 'data.key.remoteJid') ?:
            data_get($payload, 'data.remoteJid') ?:
            data_get($payload, 'body.data.key.remoteJid') ?:
            data_get($payload, 'body.remoteJid') ?:
            data_get($payload, 'remoteJid') ?:
            data_get($payload, 'data.messages.0.key.remoteJid') ?:
            data_get($payload, 'body.data.messages.0.key.remoteJid');

        $remoteJid = is_string($remoteJid) ? trim($remoteJid) : null;

        if (!$remoteJid) {
            Log::warning('Evolution webhook: remote_jid ausente', ['instance' => $instanceName]);
            return response()->json(['ok' => true, 'ignored' => 'missing_remote_jid'], 202);
        }

        // Direção: ignora mensagens enviadas por nós (evita duplicar outbound no inbound)
        $fromMe =
            (bool) (data_get($payload, 'data.key.fromMe')
            ?? data_get($payload, 'data.messages.0.key.fromMe')
            ?? data_get($payload, 'body.data.key.fromMe')
            ?? false);

        if ($fromMe) {
            return response()->json(['ok' => true, 'ignored' => 'from_me'], 200);
        }

        // Texto (quando for mensagem de texto)
        $text =
            data_get($payload, 'data.message.conversation') ?:
            data_get($payload, 'data.message.extendedTextMessage.text') ?:
            data_get($payload, 'data.text') ?:
            data_get($payload, 'message.text') ?:
            data_get($payload, 'body.data.message.conversation') ?:
            data_get($payload, 'body.data.message.extendedTextMessage.text') ?:
            data_get($payload, 'data.messages.0.message.conversation') ?:
            data_get($payload, 'data.messages.0.message.extendedTextMessage.text');

        $text = is_string($text) ? trim($text) : null;

        // ID da mensagem
        $providerMessageId =
            data_get($payload, 'data.key.id') ?:
            data_get($payload, 'data.id') ?:
            data_get($payload, 'message.id') ?:
            data_get($payload, 'body.data.key.id') ?:
            data_get($payload, 'data.messages.0.key.id') ?:
            data_get($payload, 'body.data.messages.0.key.id');

        $providerMessageId = is_string($providerMessageId) ? trim($providerMessageId) : null;

        // Timestamp
        $ts =
            data_get($payload, 'data.messageTimestamp') ?:
            data_get($payload, 'data.timestamp') ?:
            data_get($payload, 'timestamp') ?:
            data_get($payload, 'body.data.messageTimestamp') ?:
            data_get($payload, 'data.messages.0.messageTimestamp') ?:
            data_get($payload, 'data.messages.0.message.messageTimestamp');

        $messageAt = $this->parseTimestamp($ts) ?: now();

        // Tipo
        $type = $text ? 'text' : (string) (data_get($payload, 'data.messageType') ?: 'unknown');

        // 6) Criar / atualizar Contact (opcional)
        $contactId = null;
        try {
            $phoneDigits = $this->jidToDigits((string) $remoteJid);

            if ($phoneDigits) {
                $phoneE164 = $phoneDigits; // E.164 sem '+'

                $contact = Contact::updateOrCreate(
                    ['tenant_id' => 1, 'phone_e164' => $phoneE164],
                    [
                        'name' => data_get($payload, 'data.pushName')
                            ?: data_get($payload, 'pushName')
                            ?: data_get($payload, 'sender.name')
                            ?: null,
                        'pushname' => data_get($payload, 'data.pushName') ?: null,
                        'phone_raw' => $phoneDigits,
                        'is_group' => str_contains($remoteJid, '@g.us'),
                    ]
                );

                $contactId = $contact->id;
            }
        } catch (\Throwable $e) {
            Log::warning('Evolution webhook: falha ao upsert contact', [
                'err' => $e->getMessage(),
                'remoteJid' => $remoteJid,
            ]);
        }

        // 7) Chat upsert
        $title =
            data_get($payload, 'data.pushName')
            ?: data_get($payload, 'pushName')
            ?: null;

        $chat = Chat::updateOrCreate(
            [
                'whatsapp_instance_id' => $instance->id,
                'remote_jid' => $remoteJid,
            ],
            [
                'user_id' => $instance->user_id,
                'title' => $title,
                'last_message_at' => $messageAt,
            ]
        );

        // 8) Dedup / idempotência
        if ($providerMessageId) {
            $exists = Message::query()
                ->where('chat_id', $chat->id)
                ->where('provider_message_id', $providerMessageId)
                ->exists();

            if ($exists) {
                return response()->json(['ok' => true, 'dedup' => true], 200);
            }
        } else {
            // fallback: evita duplicado quando provider id não vier
            $fingerprint = sha1($remoteJid . '|' . ($text ?? '') . '|' . $messageAt->format('Y-m-d H:i:s'));

            $exists = Message::query()
                ->where('chat_id', $chat->id)
                ->where('provider_message_id', $fingerprint)
                ->exists();

            if ($exists) {
                return response()->json(['ok' => true, 'dedup' => true], 200);
            }

            $providerMessageId = $fingerprint;
        }

        // 9) Salvar Message
        try {
            Message::create([
                'chat_id'             => $chat->id,
                'contact_id'          => $contactId,
                'provider_message_id' => $providerMessageId,
                'direction'           => 'inbound',
                'type'                => $type ?: 'unknown',
                'body'                => $text,
                'status'              => 'received',
                'message_at'          => $messageAt,
                'raw'                 => $payload,
            ]);
        } catch (\Throwable $e) {
            Log::error('Evolution webhook: falha ao salvar Message', [
                'err' => $e->getMessage(),
                'chat_id' => $chat->id ?? null,
                'provider_message_id' => $providerMessageId,
            ]);

            // responde 200 pra evitar retry infinito, mas sinaliza
            return response()->json(['ok' => true, 'saved' => false], 200);
        }

        return response()->json(['ok' => true, 'saved' => true], 200);
    }

    private function guessInstanceName(array $payload): ?string
    {
        $instanceName =
            data_get($payload, 'instance') ?:
            data_get($payload, 'instanceName') ?:
            data_get($payload, 'data.instance') ?:
            data_get($payload, 'data.instanceName') ?:
            data_get($payload, 'body.instance') ?:
            data_get($payload, 'body.instanceName') ?:
            data_get($payload, 'body.data.instance') ?:
            data_get($payload, 'body.data.instanceName');

        $instanceName = is_string($instanceName) ? trim($instanceName) : null;
        return $instanceName !== '' ? $instanceName : null;
    }

    private function guessEvent(array $payload): ?string
    {
        $event =
            data_get($payload, 'event')
            ?: data_get($payload, 'type')
            ?: data_get($payload, 'action')
            ?: data_get($payload, 'body.event')
            ?: data_get($payload, 'body.type')
            ?: data_get($payload, 'body.action');

        if (!is_string($event)) {
            return null;
        }

        $event = strtolower(trim($event));
        return $event !== '' ? $event : null;
    }

    private function parseTimestamp($ts): ?Carbon
    {
        try {
            if (is_numeric($ts)) {
                $n = (int) $ts;

                // Heurística: timestamps em ms costumam ser >= 13 dígitos
                if ($n >= 1000000000000) {
                    return Carbon::createFromTimestampMs($n);
                }

                return Carbon::createFromTimestamp($n);
            }

            if (is_string($ts) && trim($ts) !== '') {
                return Carbon::parse($ts);
            }
        } catch (\Throwable $e) {
            // ignora
        }

        return null;
    }

    private function jidToDigits(string $jid): ?string
    {
        $base = explode('@', $jid)[0] ?? '';
        $digits = preg_replace('/\D+/', '', $base);
        $digits = is_string($digits) ? $digits : '';
        return $digits !== '' ? $digits : null;
    }
}
