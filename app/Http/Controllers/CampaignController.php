<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

use App\Models\Campaign;
use App\Models\WhatsappInstance;
use App\Models\CampaignRecipient;
use App\Models\CampaignMessage;
use App\Models\Contact;

use App\Support\Settings;
use App\Jobs\ProcessCampaignDispatchJob;

class CampaignController extends Controller
{
    // -----------------------------
    // ✅ Compat helpers (Recipients)
    // -----------------------------
    private function recipientsStatusCol(): string
    {
        return Schema::hasColumn('campaign_recipients', 'status') ? 'status' : 'send_status';
    }

    private function recipientsErrorCol(): string
    {
        return Schema::hasColumn('campaign_recipients', 'validation_error') ? 'validation_error' : 'invalid_reason';
    }

    private function recipientsProcessingValue(): string
    {
        return Schema::hasColumn('campaign_recipients', 'status') ? 'processing' : 'queued';
    }

    private function recipientsFailCol(): ?string
    {
        if (Schema::hasColumn('campaign_recipients', 'fail_reason')) return 'fail_reason';
        if (Schema::hasColumn('campaign_recipients', 'last_error')) return 'last_error';
        return null;
    }

    private function resolveTenantId(): int
    {
        try {
            $u = Auth::user();
            if ($u && isset($u->tenant_id) && (int)$u->tenant_id > 0) {
                return (int)$u->tenant_id;
            }
        } catch (\Throwable $e) {}
        return 1;
    }

    // -----------------------------
    // ✅ Media helpers
    // -----------------------------
    private function detectMediaType(?string $mime, ?string $ext = null): string
    {
        $mime = strtolower((string)$mime);
        $ext  = strtolower((string)$ext);

        if (str_starts_with($mime, 'image/')) return 'image';
        if (str_starts_with($mime, 'video/')) return 'video';
        if (str_starts_with($mime, 'audio/')) return 'audio';

        if (in_array($ext, ['jpg','jpeg','png','webp','gif'])) return 'image';
        if (in_array($ext, ['mp4','3gp','3gpp','mov','mkv','webm'])) return 'video';
        if (in_array($ext, ['mp3','ogg','opus','wav','m4a','aac','amr'])) return 'audio';

        return 'document';
    }

    private function maxBytesForMediaType(string $type): int
    {
        return match ($type) {
            'image' => 5 * 1024 * 1024,
            'video' => 16 * 1024 * 1024,
            'audio' => 16 * 1024 * 1024,
            default => 100 * 1024 * 1024,
        };
    }

    private function publicUrlForStoredPath(string $path): string
    {
        // Gera URL pública baseada no APP_URL + /storage/...
        $relative = Storage::disk('public')->url($path); // ex: /storage/campaign-media/x.png
        return url($relative);
    }

    // -----------------------------
    // ✅ CRUD Campanhas
    // -----------------------------
    public function index()
    {
        $userId = Auth::id();

        $campaigns = Campaign::with(['instance'])
            ->where('user_id', $userId)
            ->orderByDesc('id')
            ->get();

        return view('campanhas.index', compact('campaigns'));
    }

    public function create()
    {
        $userId = Auth::id();

        $q = WhatsappInstance::where('user_id', $userId);

        if (Schema::hasColumn('whatsapp_instances', 'is_active')) {
            $q->where('is_active', 1);
        } elseif (Schema::hasColumn('whatsapp_instances', 'active')) {
            $q->where('active', 1);
        } elseif (Schema::hasColumn('whatsapp_instances', 'status')) {
            $q->where('status', 'active');
        }

        $instances = $q->orderByDesc('id')->get();

        return view('campanhas.create', compact('instances'));
    }

    public function store(Request $request)
    {
        $userId = Auth::id();

        $request->validate([
            'name' => 'required|string|max:160',
            'whatsapp_instance_id' => 'required|integer',
            'delay_min_ms' => 'required|integer|min:500|max:60000',
            'delay_max_ms' => 'required|integer|min:500|max:60000',
        ]);

        $instance = WhatsappInstance::where('user_id', $userId)
            ->where('id', $request->whatsapp_instance_id)
            ->firstOrFail();

        $minSeconds = max(1, (int) ceil(((int)$request->delay_min_ms) / 1000));
        $maxSeconds = max($minSeconds, (int) ceil(((int)$request->delay_max_ms) / 1000));

        $campaign = Campaign::create([
            'user_id' => $userId,
            'whatsapp_instance_id' => $instance->id,
            'name' => $request->name,
            'delay_min_seconds' => $minSeconds,
            'delay_max_seconds' => $maxSeconds,
            'status' => 'draft',
        ]);

        return redirect()->route('campanhas.show', $campaign->id)
            ->with('success', 'Campanha criada com sucesso!');
    }

    public function show($id)
    {
        $userId = Auth::id();

        $campaign = Campaign::with(['instance', 'recipients', 'messages'])
            ->where('user_id', $userId)
            ->where('id', $id)
            ->firstOrFail();

        return view('campanhas.show', compact('campaign'));
    }

    // ---------------------------------------------------------
    // ✅ Mensagens (texto + mídia)
    // ---------------------------------------------------------
    public function storeMessage(Request $request, $id)
    {
        $userId = Auth::id();

        $campaign = Campaign::where('user_id', $userId)
            ->where('id', $id)
            ->firstOrFail();

        $validated = $request->validate([
            'text' => 'nullable|string|max:5000',
            'file' => 'nullable|file|max:102400', // 100MB em KB
        ]);

        $text = trim((string)($validated['text'] ?? ''));
        $file = $request->file('file');

        if (!$file && $text === '') {
            return back()
                ->withErrors(['text' => 'Digite uma mensagem ou anexe um arquivo.'])
                ->withInput();
        }

        $nextPos = (int) CampaignMessage::where('campaign_id', $campaign->id)->max('position');
        $nextPos = $nextPos > 0 ? $nextPos + 1 : 1;

        // -------------------
        // Só texto
        // -------------------
        if (!$file) {
            CampaignMessage::create([
                'campaign_id'  => $campaign->id,
                'position'     => $nextPos,
                'primary_type' => 'text',
                'text'         => $text,
            ]);

            return redirect()->route('campanhas.show', $campaign->id)
                ->with('success', 'Mensagem de texto adicionada na campanha.');
        }

        // -------------------
        // Com arquivo
        // -------------------
        $mime = (string)($file->getMimeType() ?: $file->getClientMimeType());
        $ext  = (string)$file->getClientOriginalExtension();
        $type = $this->detectMediaType($mime, $ext);

        $maxBytes = $this->maxBytesForMediaType($type);
        $size = (int)$file->getSize();

        if ($size > $maxBytes) {
            $mb = (int) round($maxBytes / 1024 / 1024);
            return back()
                ->withErrors(['file' => "Arquivo muito grande para {$type}. Limite sugerido: {$mb}MB."])
                ->withInput();
        }

        $originalName = (string) $file->getClientOriginalName();
        $baseName = pathinfo($originalName, PATHINFO_FILENAME);
        $safeName = Str::slug($baseName);
        $safeName = $safeName !== '' ? $safeName : 'arquivo';

        $finalExt = strtolower($ext ?: ($file->guessExtension() ?: 'bin'));
        $finalName = $safeName . '-' . date('YmdHis') . '.' . $finalExt;

        $path = $file->storeAs('campaign-media', $finalName, 'public');
        $publicUrl = $this->publicUrlForStoredPath($path);

        $row = [
            'campaign_id'  => $campaign->id,
            'position'     => $nextPos,
            'primary_type' => $type,
        ];

        // caption (novo) ou text (fallback)
        if (Schema::hasColumn('campaign_messages', 'caption')) {
            $row['caption'] = $text !== '' ? $text : null;
        } else {
            $row['text'] = $text !== '' ? $text : null;
        }

        // URL pública
        if (Schema::hasColumn('campaign_messages', 'media_url')) {
            $row['media_url'] = $publicUrl;
        }

        // caminho interno (pra debug / base64 / delete)
        if (Schema::hasColumn('campaign_messages', 'media_path')) {
            $row['media_path'] = $path;
        }

        // padrão novo
        if (Schema::hasColumn('campaign_messages', 'media_type')) $row['media_type'] = $type;
        if (Schema::hasColumn('campaign_messages', 'media_mime')) $row['media_mime'] = $mime ?: null;
        if (Schema::hasColumn('campaign_messages', 'media_filename')) $row['media_filename'] = $finalName;
        if (Schema::hasColumn('campaign_messages', 'media_size')) $row['media_size'] = $size;

        // padrão alternativo/antigo
        if (Schema::hasColumn('campaign_messages', 'mime_type')) $row['mime_type'] = $mime ?: null;
        if (Schema::hasColumn('campaign_messages', 'file_name')) $row['file_name'] = $finalName;
        if (Schema::hasColumn('campaign_messages', 'file_size')) $row['file_size'] = $size;

        CampaignMessage::create($row);

        return redirect()->route('campanhas.show', $campaign->id)
            ->with('success', 'Mensagem com arquivo adicionada na campanha.');
    }

    public function destroyMessage($id, $messageId)
    {
        $userId = Auth::id();

        $campaign = Campaign::where('user_id', $userId)
            ->where('id', $id)
            ->firstOrFail();

        $msg = CampaignMessage::where('campaign_id', $campaign->id)
            ->where('id', $messageId)
            ->firstOrFail();

        // apaga arquivo se tiver media_path
        try {
            if (Schema::hasColumn('campaign_messages', 'media_path')) {
                $p = (string)($msg->media_path ?? '');
                if ($p !== '' && Storage::disk('public')->exists($p)) {
                    Storage::disk('public')->delete($p);
                }
            }
        } catch (\Throwable $e) {}

        $msg->delete();

        // reordena posições
        $msgs = CampaignMessage::where('campaign_id', $campaign->id)->orderBy('position')->get();
        $pos = 1;
        foreach ($msgs as $m) {
            if ((int)$m->position !== $pos) {
                $m->position = $pos;
                $m->save();
            }
            $pos++;
        }

        return redirect()->route('campanhas.show', $campaign->id)
            ->with('success', 'Mensagem removida.');
    }

    // ---------------------------------------------------------
    // ✅ Disparo manual
    // ---------------------------------------------------------
    public function dispatchCampaign(Request $request, $id)
    {
        $userId = Auth::id();
        $tenantId = $this->resolveTenantId();

        $statusCol = $this->recipientsStatusCol();
        $processingValue = $this->recipientsProcessingValue();
        $failCol = $this->recipientsFailCol();

        $campaign = Campaign::with(['instance'])
            ->where('user_id', $userId)
            ->where('id', $id)
            ->firstOrFail();

        $baseUrl = rtrim((string) config('evolution.base_url'), '/');
        $apiKey  = (string) config('evolution.api_key');
        $timeout = (int) config('evolution.timeout', 30);

        if (!$baseUrl || !$apiKey) {
            return redirect()->route('campanhas.show', $campaign->id)
                ->with('error', 'Evolution não configurado (base_url/api_key). Confira o .env e /debug-evo.');
        }

        $instanceName = (string) optional($campaign->instance)->instance_name;
        if (!$instanceName) {
            return redirect()->route('campanhas.show', $campaign->id)
                ->with('error', 'Campanha sem instance_name. Verifique a instância vinculada.');
        }

        $messages = CampaignMessage::where('campaign_id', $campaign->id)
            ->orderBy('position')
            ->get();

        if ($messages->count() === 0) {
            return redirect()->route('campanhas.show', $campaign->id)
                ->with('error', 'Nenhuma mensagem cadastrada na campanha.');
        }

        $delayMinDefault = max(9, (int)($campaign->delay_min_seconds ?? 9));
        $delayMaxDefault = max($delayMinDefault, (int)($campaign->delay_max_seconds ?? $delayMinDefault));

        $delayMin = max(1, Settings::int($tenantId, 'campaign.delay_min_seconds', $delayMinDefault));
        $delayMax = max($delayMin, Settings::int($tenantId, 'campaign.delay_max_seconds', $delayMaxDefault));

        $pauseEvery   = max(1, Settings::int($tenantId, 'campaign.pause_every', 20));
        $pauseSeconds = max(0, Settings::int($tenantId, 'campaign.pause_seconds', 20));

        $limitMax = max(1, Settings::int($tenantId, 'campaign.limit_max', 50));

        $limit = (int) $request->input('limit', 20);
        $limit = $limit > 0 ? min($limit, $limitMax) : min(20, $limitMax);

        $batch = CampaignRecipient::where('campaign_id', $campaign->id)
            ->where('is_valid', 1)
            ->whereIn($statusCol, ['pending', 'failed'])
            ->orderBy('id')
            ->limit($limit)
            ->get();

        if ($batch->count() === 0) {
            return redirect()->route('campanhas.show', $campaign->id)
                ->with('error', 'Nenhum destinatário válido pendente para envio.');
        }

        $sentNow = 0;
        $failedNow = 0;
        $processedNow = 0;

        try {
            $campaign->status = 'running';
            $campaign->save();
        } catch (\Throwable $e) {}

        foreach ($batch as $r) {
            $phone = (string) $r->phone_digits;
            $okAll = true;

            $r->{$statusCol} = $processingValue;
            $r->save();

            foreach ($messages as $m) {
                $ptype = (string)($m->primary_type ?? 'text');

                if ($ptype === 'text') {
                    $text = trim((string)($m->text ?? ''));
                    if ($text === '') continue;

                    $ok = $this->evolutionSendText($baseUrl, $apiKey, $timeout, $instanceName, $phone, $text);
                    if (!$ok) { $okAll = false; break; }

                } else {
                    $mediaUrl = (string)($m->media_url ?? '');
                    if ($mediaUrl === '') continue;

                    $caption  = (string)($m->caption ?? $m->text ?? '');
                    $mime     = (string)($m->media_mime ?? $m->mime_type ?? '');
                    $fileName = (string)($m->media_filename ?? $m->file_name ?? basename(parse_url($mediaUrl, PHP_URL_PATH) ?: 'arquivo'));

                    if ($ptype === 'audio') {
                        $ok = $this->evolutionSendAudio($baseUrl, $apiKey, $timeout, $instanceName, $phone, $mediaUrl);
                    } else {
                        $ok = $this->evolutionSendMedia($baseUrl, $apiKey, $timeout, $instanceName, $phone, $ptype, $mime, $caption, $mediaUrl, $fileName);
                    }

                    if (!$ok) { $okAll = false; break; }
                }

                usleep(250 * 1000);
            }

            if ($okAll) {
                $r->{$statusCol} = 'sent';
                $r->sent_at = now();
                if ($failCol) $r->{$failCol} = null;
                $sentNow++;
            } else {
                $r->{$statusCol} = 'failed';
                if ($failCol) $r->{$failCol} = 'Falha ao enviar via Evolution.';
                $failedNow++;
            }
            $r->save();

            $processedNow++;

            if ($pauseEvery > 0 && ($processedNow % $pauseEvery === 0)) {
                if ($pauseSeconds > 0) sleep($pauseSeconds);
            } else {
                $sleep = rand($delayMin, $delayMax);
                sleep($sleep);
            }
        }

        $pendingLeft = CampaignRecipient::where('campaign_id', $campaign->id)
            ->where('is_valid', 1)
            ->whereIn($statusCol, ['pending', 'failed'])
            ->count();

        if ($pendingLeft === 0) {
            try {
                $campaign->status = 'finished';
                $campaign->save();
            } catch (\Throwable $e) {}
        }

        return redirect()->route('campanhas.show', $campaign->id)
            ->with('success', "Disparo executado: {$sentNow} enviados, {$failedNow} falharam. Restantes: {$pendingLeft}.");
    }

    // ---------------------------------------------------------
    // ✅ Disparo automático (fila)
    // ---------------------------------------------------------
    public function dispatchCampaignAuto(Request $request, $id)
    {
        $userId = Auth::id();
        $statusCol = $this->recipientsStatusCol();

        $campaign = Campaign::with(['instance'])
            ->where('user_id', $userId)
            ->where('id', $id)
            ->firstOrFail();

        $baseUrl = rtrim((string) config('evolution.base_url'), '/');
        $apiKey  = (string) config('evolution.api_key');

        if (!$baseUrl || !$apiKey) {
            return redirect()->route('campanhas.show', $campaign->id)
                ->with('error', 'Evolution não configurado (base_url/api_key). Confira o .env e /debug-evo.');
        }

        $instanceName = (string) optional($campaign->instance)->instance_name;
        if (!$instanceName) {
            return redirect()->route('campanhas.show', $campaign->id)
                ->with('error', 'Campanha sem instance_name. Verifique a instância vinculada.');
        }

        $msgCount = CampaignMessage::where('campaign_id', $campaign->id)->count();
        if ($msgCount === 0) {
            return redirect()->route('campanhas.show', $campaign->id)
                ->with('error', 'Cadastre ao menos 1 mensagem (texto ou arquivo) para iniciar o disparo automático.');
        }

        $pending = CampaignRecipient::where('campaign_id', $campaign->id)
            ->where('is_valid', 1)
            ->whereIn($statusCol, ['pending', 'failed'])
            ->count();

        if ($pending === 0) {
            return redirect()->route('campanhas.show', $campaign->id)
                ->with('error', 'Nenhum destinatário válido pendente/falhado para enviar.');
        }

        $campaign->status = 'running';
        $campaign->save();

        ProcessCampaignDispatchJob::dispatch($campaign->id, $userId)
            ;

        return redirect()->route('campanhas.show', $campaign->id)
            ->with('success', 'Disparo automático iniciado! Pode sair da tela, ele continuará rodando.');
    }

    // ---------------------------------------------------------
    // ✅ Status (JSON)
    // ---------------------------------------------------------
    public function dispatchStatus($id)
    {
        $userId = Auth::id();

        $statusCol = $this->recipientsStatusCol();
        $processingValue = $this->recipientsProcessingValue();

        $campaign = Campaign::where('user_id', $userId)
            ->where('id', $id)
            ->firstOrFail();

        $totalValid = CampaignRecipient::where('campaign_id', $campaign->id)
            ->where('is_valid', 1)
            ->count();

        $sent = CampaignRecipient::where('campaign_id', $campaign->id)->where($statusCol, 'sent')->count();
        $failed = CampaignRecipient::where('campaign_id', $campaign->id)->where($statusCol, 'failed')->count();
        $pending = CampaignRecipient::where('campaign_id', $campaign->id)->where($statusCol, 'pending')->count();
        $processing = CampaignRecipient::where('campaign_id', $campaign->id)->where($statusCol, $processingValue)->count();

        $remaining = CampaignRecipient::where('campaign_id', $campaign->id)
            ->where('is_valid', 1)
            ->whereIn($statusCol, ['pending', 'failed'])
            ->count();

        $sentToday = CampaignRecipient::query()
            ->join('campaigns', 'campaign_recipients.campaign_id', '=', 'campaigns.id')
            ->where('campaigns.user_id', $userId)
            ->where("campaign_recipients.$statusCol", 'sent')
            ->whereDate('campaign_recipients.sent_at', now()->toDateString())
            ->count();

        $dailyLimit = 200;
        $quotaLeft = max(0, $dailyLimit - (int)$sentToday);

        return response()->json([
            'status' => (string)($campaign->status ?? 'draft'),
            'total' => (int)$totalValid,
            'sent' => (int)$sent,
            'failed' => (int)$failed,
            'pending' => (int)$pending,
            'processing' => (int)$processing,
            'remaining' => (int)$remaining,
            'sent_today' => (int)$sentToday,
            'daily_limit' => (int)$dailyLimit,
            'quota_left' => (int)$quotaLeft,
        ]);
    }

    // ---------------------------------------------------------
    // ✅ Importar destinatários
    // ---------------------------------------------------------
    public function importRecipients(Request $request, $id)
    {
        $userId = Auth::id();
        $tenantId = $this->resolveTenantId();

        $statusCol = $this->recipientsStatusCol();
        $errCol = $this->recipientsErrorCol();

        $campaign = Campaign::where('user_id', $userId)
            ->where('id', $id)
            ->firstOrFail();

        $data = $request->validate([
            'ddi55' => 'nullable|in:1',
            'content' => 'nullable|string',
            'file' => 'nullable|file|max:4096',
        ]);

        $addDdi55 = isset($data['ddi55']) && $data['ddi55'] === '1';
        $rawText = trim((string)($data['content'] ?? ''));

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileText = @file_get_contents($file->getRealPath());
            if (is_string($fileText) && trim($fileText) !== '') {
                $rawText = trim($rawText . "\n" . $fileText);
            }
        }

        if ($rawText === '') {
            return back()->with('error', 'Cole números no campo ou envie um arquivo .txt/.csv.');
        }

        $lines = preg_split("/\r\n|\n|\r/", $rawText);

        $invalid = 0;
        $duplicatesInFile = 0;

        $seenDigits = [];
        $contactsUpsert = [];
        $recipientRows = [];

        $now = now();

        foreach ($lines as $line) {
            $line = trim((string)$line);
            if ($line === '') continue;

            $name = null;
            $phoneRaw = $line;

            $parts = preg_split('/[;,|\t]/', $line);
            $parts = array_values(array_filter(array_map('trim', $parts), fn($v) => $v !== ''));

            if (count($parts) >= 2) {
                $p0 = $parts[0];
                $p1 = $parts[1];

                $d0 = preg_replace('/\D+/', '', $p0);
                $d1 = preg_replace('/\D+/', '', $p1);

                if (strlen($d0) >= 10 && strlen($d0) <= 15) {
                    $phoneRaw = $p0;
                    $name = $p1;
                } elseif (strlen($d1) >= 10 && strlen($d1) <= 15) {
                    $phoneRaw = $p1;
                    $name = $p0;
                } else {
                    $name = $p0;
                    $phoneRaw = $p1;
                }
            }

            $norm = $this->normalizePhoneDigits($phoneRaw, $addDdi55);

            $isValid = (bool) $norm['is_valid'];
            $digits  = (string) ($norm['digits'] ?? '');
            $err     = (string) ($norm['error'] ?? '');

            if ($digits === '') {
                $invalid++;
                continue;
            }

            if (isset($seenDigits[$digits])) {
                $duplicatesInFile++;
                continue;
            }
            $seenDigits[$digits] = true;

            $e164 = '+' . ltrim($digits, '+');

            $contactsUpsert[] = [
                'tenant_id' => $tenantId,
                'phone_e164' => mb_substr($e164, 0, 20),
                'name' => $name ? mb_substr($name, 0, 160) : null,
                'pushname' => null,
                'phone_raw' => mb_substr((string)$phoneRaw, 0, 80),
                'profile_pic_url' => null,
                'email' => null,
                'is_group' => 0,
                'metadata' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $row = [
                'campaign_id' => $campaign->id,
                'name' => $name ? mb_substr($name, 0, 160) : null,
                'phone_raw' => mb_substr((string)$phoneRaw, 0, 64),
                'phone_digits' => mb_substr($digits, 0, 32),
                'is_valid' => $isValid ? 1 : 0,
                $errCol => $isValid ? null : mb_substr($err, 0, 190),
                $statusCol => 'pending',
                'created_at' => $now,
                'updated_at' => $now,
            ];

            $recipientRows[] = $row;
            if (!$isValid) $invalid++;
        }

        if (empty($recipientRows)) {
            return redirect()->route('campanhas.recipients', $campaign->id)
                ->with('error', 'Nenhum número válido/normalizável encontrado para importar.');
        }

        $digitsList = array_values(array_unique(array_map(fn($r) => $r['phone_digits'], $recipientRows)));

        $already = CampaignRecipient::where('campaign_id', $campaign->id)
            ->whereIn('phone_digits', $digitsList)
            ->pluck('phone_digits')
            ->all();

        $alreadySet = array_fill_keys($already, true);

        $filteredRecipients = [];
        $duplicatesInDb = 0;

        foreach ($recipientRows as $row) {
            if (isset($alreadySet[$row['phone_digits']])) {
                $duplicatesInDb++;
                continue;
            }
            $filteredRecipients[] = $row;
        }

        if (!empty($contactsUpsert)) {
            $tmp = [];
            foreach ($contactsUpsert as $c) {
                $k = $c['tenant_id'] . '|' . $c['phone_e164'];
                $tmp[$k] = $c;
            }
            $contactsUpsert = array_values($tmp);

            Contact::upsert(
                $contactsUpsert,
                ['tenant_id', 'phone_e164'],
                ['name', 'phone_raw', 'updated_at']
            );
        }

        $inserted = 0;

        if (!empty($filteredRecipients)) {
            foreach (array_chunk($filteredRecipients, 500) as $chunk) {
                CampaignRecipient::insertOrIgnore($chunk);
                $inserted += count($chunk);
            }
        }

        $duplicates = $duplicatesInFile + $duplicatesInDb;

        return redirect()->route('campanhas.recipients', $campaign->id)
            ->with('success', "Importação concluída: {$inserted} adicionados, {$duplicates} duplicados, {$invalid} inválidos.");
    }

    private function normalizePhoneDigits(string $raw, bool $addDdi55): array
    {
        $digits = preg_replace('/\D+/', '', (string)$raw);
        $digits = ltrim($digits, '0');

        if ($digits === '') {
            return ['digits' => null, 'is_valid' => false, 'error' => 'Sem dígitos.'];
        }

        if ($addDdi55) {
            if (strlen($digits) === 10 || strlen($digits) === 11) {
                $digits = '55' . $digits;
            }
        }

        $isValid = false;
        $err = null;

        if (str_starts_with($digits, '55') && (strlen($digits) === 12 || strlen($digits) === 13)) {
            $isValid = true;
        } else {
            $err = 'Formato inesperado (esperado 55 + DDD + número).';
        }

        return ['digits' => $digits, 'is_valid' => $isValid, 'error' => $err];
    }

    // ---------------------------------------------------------
    // ✅ Evolution API calls
    // ---------------------------------------------------------
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

            Log::warning('Evolution sendText failed', [
                'status' => $resp->status(),
                'body' => substr($resp->body(), 0, 500),
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('Evolution sendText exception', ['error' => $e->getMessage()]);
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
        string $mediaUrl,
        string $fileName
    ): bool {
        $url = $baseUrl . '/message/sendMedia/' . urlencode($instanceName);

        try {
            $payload = [
                'number'    => $phoneDigits,
                'mediatype' => $mediaType,
                'mimetype'  => $mimeType ?: 'application/octet-stream',
                'caption'   => $caption,
                'media'     => $mediaUrl,
                'fileName'  => $fileName,
            ];

            $resp = Http::timeout($timeout)
                ->withHeaders([
                    'apikey' => $apiKey,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($url, $payload);

            if ($resp->successful()) return true;

            Log::warning('Evolution sendMedia failed', [
                'status' => $resp->status(),
                'body' => substr($resp->body(), 0, 500),
                'media_url' => $mediaUrl,
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('Evolution sendMedia exception', ['error' => $e->getMessage()]);
            return false;
        }
    }

    private function evolutionSendAudio(
        string $baseUrl,
        string $apiKey,
        int $timeout,
        string $instanceName,
        string $phoneDigits,
        string $audioUrl
    ): bool {
        $url = $baseUrl . '/message/sendWhatsAppAudio/' . urlencode($instanceName);

        try {
            $payload = [
                'number' => $phoneDigits,
                'audio'  => $audioUrl,
            ];

            $resp = Http::timeout($timeout)
                ->withHeaders([
                    'apikey' => $apiKey,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])
                ->post($url, $payload);

            if ($resp->successful()) return true;

            Log::warning('Evolution sendAudio failed', [
                'status' => $resp->status(),
                'body' => substr($resp->body(), 0, 500),
                'audio_url' => $audioUrl,
            ]);

            return false;
        } catch (\Throwable $e) {
            Log::error('Evolution sendAudio exception', ['error' => $e->getMessage()]);
            return false;
        }
    }
}
