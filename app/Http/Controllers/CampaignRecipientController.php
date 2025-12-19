<?php

namespace App\Http\Controllers;

use App\Models\Campaign;
use App\Models\CampaignRecipient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CampaignRecipientController extends Controller
{
    public function index(Request $request, Campaign $campaign)
    {
        // ✅ Segurança: campanha precisa ser do usuário logado
        abort_unless((int) $campaign->user_id === (int) Auth::id(), 403);

        $q = trim((string) $request->get('q', ''));

        // Filtros opcionais (não obrigatórios na view, mas já prontos)
        $status = trim((string) $request->get('status', '')); // pending|sent|failed
        $valid  = $request->get('valid', null); // 1|0|null

        $recipientsQuery = CampaignRecipient::query()
            ->where('campaign_id', $campaign->id)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('name', 'like', "%{$q}%")
                        ->orWhere('phone_raw', 'like', "%{$q}%")
                        ->orWhere('phone_digits', 'like', "%{$q}%");
                });
            })
            ->when($status !== '', function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when($valid !== null && $valid !== '', function ($query) use ($valid) {
                $query->where('is_valid', (int) $valid);
            })
            ->orderByDesc('id');

        $recipients = $recipientsQuery
            ->paginate(50)
            ->withQueryString();

        // ✅ Contadores (sem precisar carregar tudo em memória)
        $total   = CampaignRecipient::where('campaign_id', $campaign->id)->count();
        $validC  = CampaignRecipient::where('campaign_id', $campaign->id)->where('is_valid', 1)->count();
        $invalid = $total - $validC;

        $pending = CampaignRecipient::where('campaign_id', $campaign->id)->where('status', 'pending')->count();
        $sent    = CampaignRecipient::where('campaign_id', $campaign->id)->where('status', 'sent')->count();
        $failed  = CampaignRecipient::where('campaign_id', $campaign->id)->where('status', 'failed')->count();

        // ✅ View correta: resources/views/campanhas/recipients.blade.php
        return view('campanhas.recipients', [
            'campaign'   => $campaign,
            'recipients' => $recipients,
            'total'      => $total,
            'valid'      => $validC,
            'invalid'    => $invalid,
            'pending'    => $pending,
            'sent'       => $sent,
            'failed'     => $failed,
            'q'          => $q,
            'status'     => $status,
            'validFilter'=> $valid,
        ]);
    }

    public function destroy(Campaign $campaign, CampaignRecipient $recipient)
    {
        // ✅ Segurança: campanha precisa ser do usuário logado
        abort_unless((int) $campaign->user_id === (int) Auth::id(), 403);

        // ✅ Garante que o recipient pertence à campanha
        abort_unless((int) $recipient->campaign_id === (int) $campaign->id, 404);

        $recipient->delete();

        return redirect()
            ->route('campanhas.recipients', $campaign->id)
            ->with('success', 'Contato removido da campanha.');
    }

    public function dedup(Campaign $campaign)
    {
        // ✅ Segurança: campanha precisa ser do usuário logado
        abort_unless((int) $campaign->user_id === (int) Auth::id(), 403);

        // Dedup por phone_digits mantendo o menor ID
        $deleted = 0;

        DB::transaction(function () use ($campaign, &$deleted) {

            // Busca duplicados por phone_digits (ignora null/vazio)
            $dups = CampaignRecipient::selectRaw('phone_digits, MIN(id) as keep_id, COUNT(*) as c')
                ->where('campaign_id', $campaign->id)
                ->whereNotNull('phone_digits')
                ->where('phone_digits', '!=', '')
                ->groupBy('phone_digits')
                ->havingRaw('COUNT(*) > 1')
                ->get();

            foreach ($dups as $d) {
                $deleted += CampaignRecipient::where('campaign_id', $campaign->id)
                    ->where('phone_digits', $d->phone_digits)
                    ->where('id', '!=', $d->keep_id)
                    ->delete();
            }
        });

        return redirect()
            ->route('campanhas.recipients', $campaign->id)
            ->with('success', "Deduplicação concluída. Removidos: {$deleted} duplicados.");
    }
}
