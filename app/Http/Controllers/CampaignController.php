<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

use App\Models\Campaign;
use App\Models\WhatsappInstance;
use App\Models\CampaignRecipient;
use App\Models\CampaignMessage;
use App\Models\Contact;

use App\Support\Settings;

class CampaignController extends Controller
{
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

        // ✅ Compatível com bancos diferentes:
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

        $minSeconds = max(1, (int) ceil(((int) $request->delay_min_ms) / 1000));
        $maxSeconds = max($minSeconds, (int) ceil(((int) $request->delay_max_ms) / 1000));

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

        $campaign = Campaign::with([
                'instance',
                'recipients',
                'messages',
            ])
            ->where('user_id', $userId)
            ->where('id', $id)
            ->firstOrFail();

        return view('campanhas.show', compact('campaign'));
    }

    // ---------------------------------------------------------
    // ✅ Tela de destinatários
    // ---------------------------------------------------------
    public function recipients(Request $request, $id)
    {
        $userId = Auth::id();

        $campaign = Campaign::where('user_id', $userId)
            ->where('id', $id)
            ->firstOrFail();

        $q = trim((string)$request->get('q', ''));

        $recipients = CampaignRecipient::where('campaign_id', $campaign->id)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('name', 'like', "%{$q}%")
                       ->orWhere('phone_digits', 'like', "%{$q}%")
                       ->orWhere('phone_raw', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        $total = CampaignRecipient::where('campaign_id', $campaign->id)->count();
        $valid = CampaignRecipient::where('campaign_id', $campaign->id)->where('is_valid', 1)->count();
        $invalid = $total - $valid;

        return view('campanhas.recipients', compact('campaign', 'recipients', 'total', 'valid', 'invalid', 'q'));
    }

    public function destroyRecipient($id, $recipientId)
    {
        $userId = Auth::id();

        $campaign = Campaign::where('user_id', $userId)
            ->where('id', $id)
            ->firstOrFail();

        $recipient = CampaignRecipient::where('campaign_id', $campaign->id)
            ->where('id', $recipientId)
            ->firstOrFail();

        $recipient->delete();

        return redirect()->route('campanhas.recipients', $campaign->id)
            ->with('success', 'Contato removido da campanha.');
    }

    public function dedupRecipients($id)
    {
        $userId = Auth::id();

        $campaign = Campaign::where('user_id', $userId)
            ->where('id', $id)
            ->firstOrFail();

        $dups = CampaignRecipient::selectRaw('phone_digits, MIN(id) as keep_id, COUNT(*) as total')
            ->where('campaign_id', $campaign->id)
            ->whereNotNull('phone_digits')
            ->where('phone_digits', '!=', '')
            ->groupBy('phone_digits')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        $deleted = 0;

        foreach ($dups as $d) {
            $deleted += CampaignRecipient::where('campaign_id', $campaign->id)
                ->where('phone_digits', $d->phone_digits)
                ->where('id', '!=', $d->keep_id)
                ->delete();
        }

        return redirect()->route('campanhas.recipients', $campaign->id)
            ->with('success', "Revisão concluída: removidos {$deleted} contatos duplicados.");
    }

    // ---------------------------------------------------------
    // ✅ Mensagens
    // ---------------------------------------------------------
    public function storeMessage(Request $request, $id)
    {
        $userId = Auth::id();

        $campaign = Campaign::where('user_id', $userId)
            ->where('id', $id)
            ->firstOrFail();

        $data = $request->validate([
            'text' => 'required|string|max:5000',
        ]);

        $nextPos = (int) CampaignMessage::where('campaign_id', $campaign->id)->max('position');
        $nextPos = $nextPos > 0 ? $nextPos + 1 : 1;

        CampaignMessage::create([
            'campaign_id'  => $campaign->id,
            'position'     => $nextPos,
            'primary_type' => 'text',
            'text'         => $data['text'],
        ]);

        return redirect()->route('campanhas.show', $campaign->id)
            ->with('success', 'Mensagem adicionada na campanha.');
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

        $msg->delete();

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
    // ✅ Disparo (agora lendo settings)
    // ---------------------------------------------------------
    public function dispatchCampaign(Request $request, $id)
    {
        $userId = Auth::id();
        $tenantId = $this->resolveTenantId();

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

        // ✅ Settings (com fallback)
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
            ->whereIn('status', ['pending', 'failed'])
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
        } catch (\Throwable $e) {
            // ignora
        }

        foreach ($batch as $r) {
            $phone = (string) $r->phone_digits;
            $okAll = true;

            foreach ($messages as $m) {
                if (($m->primary_type ?? 'text') !== 'text') continue;

                $text = (string)($m->text ?? '');
                if (trim($text) === '') continue;

                $ok = $this->evolutionSendText($baseUrl, $apiKey, $timeout, $instanceName, $phone, $text);
                if (!$ok) {
                    $okAll = false;
                    break;
                }

                usleep(200 * 1000);
            }

            if ($okAll) {
                $r->status = 'sent';
                $sentNow++;
            } else {
                $r->status = 'failed';
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
            ->whereIn('status', ['pending', 'failed'])
            ->count();

        if ($pendingLeft === 0) {
            try {
                $campaign->status = 'finished';
                $campaign->save();
            } catch (\Throwable $e) {
                // ignora
            }
        }

        return redirect()->route('campanhas.show', $campaign->id)
            ->with('success', "Disparo executado: {$sentNow} enviados, {$failedNow} falharam. Restantes: {$pendingLeft}.");
    }

    // ---------------------------------------------------------
    // ✅ Importar/colar destinatários (contacts + insertOrIgnore)
    // ---------------------------------------------------------
    public function importRecipients(Request $request, $id)
    {
        $userId = Auth::id();
        $tenantId = $this->resolveTenantId();

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

            $recipientRows[] = [
                'campaign_id' => $campaign->id,
                'name' => $name ? mb_substr($name, 0, 160) : null,
                'phone_raw' => mb_substr((string)$phoneRaw, 0, 80),
                'phone_digits' => mb_substr($digits, 0, 32),
                'is_valid' => $isValid ? 1 : 0,
                'validation_error' => $isValid ? null : mb_substr($err, 0, 200),
                'status' => 'pending',
                'created_at' => $now,
                'updated_at' => $now,
            ];

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
                $inserted += count($chunk); // “tentados”
            }
        }

        $duplicates = $duplicatesInFile + $duplicatesInDb;

        return redirect()->route('campanhas.recipients', $campaign->id)
            ->with('success', "Importação concluída: {$inserted} adicionados, {$duplicates} duplicados, {$invalid} inválidos.");
    }

    private function resolveTenantId(): int
    {
        try {
            $u = Auth::user();
            if ($u && isset($u->tenant_id) && (int)$u->tenant_id > 0) {
                return (int)$u->tenant_id;
            }
        } catch (\Throwable $e) {
            // ignora
        }

        return 1;
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

            if ($resp->successful()) {
                return true;
            }

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
}
