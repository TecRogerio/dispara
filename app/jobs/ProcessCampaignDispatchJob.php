<?php

namespace App\Jobs;

use App\Models\Campaign;
use App\Models\CampaignMessage;
use App\Models\CampaignRecipient;
use App\Support\Settings;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class ProcessCampaignDispatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $campaignId;
    public int $userId;

    public int $timeout = 120;
    public int $tries = 1;

    private const DAILY_LIMIT = 200;

    private static ?string $statusCol = null;
    private static ?string $failCol = null;

    public function __construct(int $campaignId, int $userId)
    {
        $this->campaignId = $campaignId;
        $this->userId = $userId;
    }

    private function statusColumn(): string
    {
        if (self::$statusCol !== null) return self::$statusCol;

        self::$statusCol = Schema::hasColumn('campaign_recipients', 'status')
            ? 'status'
            : 'send_status';

        return self::$statusCol;
    }

    private function processingValue(): string
    {
        return $this->statusColumn() === 'status' ? 'processing' : 'queued';
    }

    private function failColumn(): ?string
    {
        if (self::$failCol !== null) return self::$failCol;

        if (Schema::hasColumn('campaign_recipients', 'fail_reason')) {
            self::$failCol = 'fail_reason';
        } elseif (Schema::hasColumn('campaign_recipients', 'last_error')) {
            self::$failCol = 'last_error';
        } else {
            self::$failCol = null;
        }

        return self::$failCol;
    }

    public function handle(): void
    {
        $campaign = Campaign::with(['instance'])
            ->where('id', $this->campaignId)
            ->where('user_id', $this->userId)
            ->first();

        if (!$campaign) return;

        $status = (string)($campaign->status ?? 'draft');
        if ($status === 'finished') return;

        $tenantId = $this->resolveTenantIdFromCampaign($campaign);

        $baseUrl = rtrim((string) config('evolution.base_url'), '/');
        $apiKey  = (string) config('evolution.api_key');
        $timeout = (int) config('evolution.timeout', 30);

        if (!$baseUrl || !$apiKey) {
            $campaign->status = 'failed';
            $campaign->save();
            return;
        }

        $instanceName = (string) optional($campaign->instance)->instance_name;
        if (!$instanceName) {
            $campaign->status = 'failed';
            $campaign->save();
            return;
        }

        $messages = CampaignMessage::where('campaign_id', $campaign->id)
            ->orderBy('position')
            ->get()
            ->filter(function ($m) {
                $ptype = (string)($m->primary_type ?? 'text');

                if ($ptype === 'text') {
                    return trim((string)($m->text ?? '')) !== '';
                }

                return trim((string)($m->media_url ?? '')) !== '' || trim((string)($m->media_path ?? '')) !== '';
            })
            ->values();

        if ($messages->count() === 0) {
            $campaign->status = 'failed';
            $campaign->save();
            return;
        }

        $sentToday = $this->countSentToday($this->userId);
        $quotaLeft = max(0, self::DAILY_LIMIT - $sentToday);

        if ($quotaLeft <= 0) {
            $campaign->status = 'paused';
            $campaign->save();
            return;
        }

        if ($status !== 'running') {
            $campaign->status = 'running';
            $campaign->save();
        }

        $recipient = $this->claimOneRecipient($campaign->id);

        if (!$recipient) {
            $remaining = $this->remainingCount($campaign->id);
            if ($remaining === 0) {
                $campaign->status = 'finished';
                $campaign->save();
            }
            return;
        }

        $statusCol = $this->statusColumn();
        $failCol   = $this->failColumn();

        $okAll = true;

        // Delays (anti-ban / comportamento humano)
        // - Entre mensagens para o MESMO número: 3 a 5 segundos (configurável)
        // - Entre um número e outro (próximo recipient): 8 segundos (configurável)
        $perMsgMinSeconds = max(0, (int) \App\Support\Settings::int('campaign.message_delay_min_seconds', 3));
        $perMsgMaxSeconds = max($perMsgMinSeconds, (int) \App\Support\Settings::int('campaign.message_delay_max_seconds', 5));
        $recipientGapSeconds = (int) \App\Support\Settings::int('campaign.recipient_gap_seconds', 8);
        if ($recipientGapSeconds < 0) { $recipientGapSeconds = 0; }

        $totalMessages = count($messages);
        $msgIndex = 0;

        foreach ($messages as $m) {
            $ptype = (string)($m->primary_type ?? 'text');

            if ($ptype === 'text') {
                $text = (string)($m->text ?? '');

                $ok = $this->evolutionSendText(
                    $baseUrl,
                    $apiKey,
                    $timeout,
                    $instanceName,
                    (string)$recipient->phone_digits,
                    $text
                );
            } else {
                $mediaUrl = (string)($m->media_url ?? '');
                $mediaPath = (string)($m->media_path ?? '');

                $caption  = (string)($m->caption ?? $m->text ?? '');
                $mime     = (string)($m->media_mime ?? $m->mime_type ?? 'application/octet-stream');
                $fileName = (string)($m->media_filename ?? $m->file_name ?? basename(parse_url($mediaUrl, PHP_URL_PATH) ?: 'arquivo'));

                // ✅ Se URL for local/interna e tiver path, tenta base64 (ajuda muito em DEV)
                $mediaValue = $mediaUrl;

                if (($mediaValue === '' || $this->shouldSendMediaAsBase64($mediaValue)) && $mediaPath !== '') {
                    $b64 = $this->buildBase64FromPublicDisk($mediaPath, $mime);
                    if ($b64) {
                        $mediaValue = $b64;
                    }
                }

                if ($ptype === 'audio') {
                    // muitos setups aceitam base64 em 'audio' também; se não aceitar, vai logar
                    $audioValue = $mediaValue !== '' ? $mediaValue : $mediaUrl;

                    $ok = $this->evolutionSendAudio(
                        $baseUrl,
                        $apiKey,
                        $timeout,
                        $instanceName,
                        (string)$recipient->phone_digits,
                        $audioValue
                    );
                } else {
                    $ok = $this->evolutionSendMedia(
                        $baseUrl,
                        $apiKey,
                        $timeout,
                        $instanceName,
                        (string)$recipient->phone_digits,
                        $ptype,
                        $mime,
                        $caption,
                        $mediaValue,
                        $fileName
                    );
                }
            }

            if (!$ok) {
                $okAll = false;
                break;
            }

            // Espera entre mensagens (mesmo número)
            $msgIndex++;
            if ($msgIndex < $totalMessages) {
                $wait = ($perMsgMaxSeconds > 0) ? random_int($perMsgMinSeconds, $perMsgMaxSeconds) : 0;
                if ($wait > 0) {
                    sleep($wait);
                }
            }
        }

        if ($okAll) {
            $recipient->{$statusCol} = 'sent';
            $recipient->sent_at = now();
            if ($failCol) $recipient->{$failCol} = null;
        } else {
            $recipient->{$statusCol} = 'failed';
            if ($failCol) $recipient->{$failCol} = 'Falha ao enviar uma das mensagens via Evolution.';
        }

        $recipient->save();

        $remaining = $this->remainingCount($campaign->id);
        if ($remaining === 0) {
            $campaign->status = 'finished';
            $campaign->save();
            return;
        }

        // Gap entre um número e outro (por padrão 8s, dentro do recomendado)
        // Você pode sobrescrever via Settings:
        // - campaign.recipient_gap_seconds (padrão 8)
        // Ou por campanha:
        // - campanhas.delay_min_seconds / campanhas.delay_max_seconds
        $recipientGapDefault = max(1, (int) Settings::int($tenantId, 'campaign.recipient_gap_seconds', 8));

        $delayMinDefault = max(1, (int)($campaign->delay_min_seconds ?? $recipientGapDefault));
        $delayMaxDefault = max($delayMinDefault, (int)($campaign->delay_max_seconds ?? $recipientGapDefault));

        // Se quiser FIXO, deixe min=max (=8 por padrão)
        $delayMin = $delayMinDefault;
        $delayMax = $delayMaxDefault;
$pauseEvery   = max(1, Settings::int($tenantId, 'campaign.pause_every', 20));
        $pauseSeconds = max(0, Settings::int($tenantId, 'campaign.pause_seconds', 20));

        $sentCount = CampaignRecipient::where('campaign_id', $campaign->id)
            ->where($statusCol, 'sent')
            ->count();

        $delay = rand($delayMin, $delayMax);
        if ($pauseEvery > 0 && $sentCount > 0 && ($sentCount % $pauseEvery === 0)) {
            $delay = max($delay, $pauseSeconds);
        }

        self::dispatch($campaign->id, $this->userId)
            ->delay(now()->addSeconds($delay))
            ;
    }

    private function shouldSendMediaAsBase64(string $mediaUrl): bool
    {
        $host = (string) (parse_url($mediaUrl, PHP_URL_HOST) ?? '');
        if ($host === '') return true;

        return in_array($host, ['localhost', '127.0.0.1', 'host.docker.internal'], true);
    }

    private function buildBase64FromPublicDisk(string $path, string $mime): ?string
    {
        try {
            if (!Storage::disk('public')->exists($path)) return null;
            $bin = Storage::disk('public')->get($path);
            $b64 = base64_encode($bin);
            return "data:{$mime};base64,{$b64}";
        } catch (\Throwable $e) {
            Log::warning('Campaign Job base64 build failed', [
                'campaign_id' => $this->campaignId,
                'path' => $path,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    private function remainingCount(int $campaignId): int
    {
        $col = $this->statusColumn();

        return (int) CampaignRecipient::where('campaign_id', $campaignId)
            ->where('is_valid', 1)
            ->whereIn($col, ['pending', 'failed'])
            ->count();
    }

    private function claimOneRecipient(int $campaignId): ?CampaignRecipient
    {
        $col = $this->statusColumn();
        $inProgressValue = $this->processingValue();

        return DB::transaction(function () use ($campaignId, $col, $inProgressValue) {
            $r = CampaignRecipient::where('campaign_id', $campaignId)
                ->where('is_valid', 1)
                ->whereIn($col, ['pending', 'failed'])
                ->orderBy('id')
                ->limit(1)
                ->lockForUpdate()
                ->first();

            if (!$r) return null;

            $r->{$col} = $inProgressValue;
            $r->save();

            return $r;
        });
    }

    private function countSentToday(int $userId): int
    {
        $col = $this->statusColumn();

        return (int) CampaignRecipient::query()
            ->join('campaigns', 'campaign_recipients.campaign_id', '=', 'campaigns.id')
            ->where('campaigns.user_id', $userId)
            ->where('campaign_recipients.' . $col, 'sent')
            ->whereDate('campaign_recipients.sent_at', now()->toDateString())
            ->count();
    }

    private function resolveTenantIdFromCampaign(Campaign $campaign): int
    {
        try {
            if (isset($campaign->tenant_id) && (int)$campaign->tenant_id > 0) {
                return (int)$campaign->tenant_id;
            }
        } catch (\Throwable $e) {}
        return 1;
    }

    private function evolutionSendText(string $baseUrl, string $apiKey, int $timeout, string $instanceName, string $phoneDigits, string $text): bool
    {
        $url = $baseUrl . '/message/sendText/' . urlencode($instanceName);

        try {
            $resp = Http::timeout($timeout)
                ->withHeaders([
                    'apikey' => $apiKey,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($url, [
                    'number' => $phoneDigits,
                    'text'   => $text,
                ]);

            if ($resp->successful()) return true;

            Log::warning('Campaign Job sendText failed', [
                'campaign_id' => $this->campaignId,
                'status' => $resp->status(),
                'body' => substr($resp->body(), 0, 800),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('Campaign Job sendText exception', [
                'campaign_id' => $this->campaignId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    private function evolutionSendMedia(
        string $baseUrl,
        string $apiKey,
        int $timeout,
        string $instanceName,
        string $phoneDigits,
        string $mediaType,
        string $mimeType,
        string $caption,
        string $mediaValue,
        string $fileName
    ): bool {
        $url = $baseUrl . '/message/sendMedia/' . urlencode($instanceName);

        try {
            $resp = Http::timeout($timeout)
                ->withHeaders([
                    'apikey' => $apiKey,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($url, [
                    'number'    => $phoneDigits,
                    'mediatype' => $mediaType,
                    'mimetype'  => $mimeType ?: 'application/octet-stream',
                    'caption'   => $caption,
                    'media'     => $mediaValue,
                    'fileName'  => $fileName,
                ]);

            if ($resp->successful()) return true;

            Log::warning('Campaign Job sendMedia failed', [
                'campaign_id' => $this->campaignId,
                'status' => $resp->status(),
                'body' => substr($resp->body(), 0, 800),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('Campaign Job sendMedia exception', [
                'campaign_id' => $this->campaignId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    private function evolutionSendAudio(
        string $baseUrl,
        string $apiKey,
        int $timeout,
        string $instanceName,
        string $phoneDigits,
        string $audioValue
    ): bool {
        $url = $baseUrl . '/message/sendWhatsAppAudio/' . urlencode($instanceName);

        try {
            $resp = Http::timeout($timeout)
                ->withHeaders([
                    'apikey' => $apiKey,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($url, [
                    'number' => $phoneDigits,
                    'audio'  => $audioValue,
                ]);

            if ($resp->successful()) return true;

            Log::warning('Campaign Job sendAudio failed', [
                'campaign_id' => $this->campaignId,
                'status' => $resp->status(),
                'body' => substr($resp->body(), 0, 800),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('Campaign Job sendAudio exception', [
                'campaign_id' => $this->campaignId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }
}
