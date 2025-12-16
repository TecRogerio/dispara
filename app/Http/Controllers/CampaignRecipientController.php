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
        abort_unless((int)$campaign->user_id === (int)Auth::id(), 403);

        $q = trim((string) $request->get('q', ''));

        $recipients = CampaignRecipient::query()
            ->where('campaign_id', $campaign->id)
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('name', 'like', "%{$q}%")
                       ->orWhere('phone_raw', 'like', "%{$q}%")
                       ->orWhere('phone_digits', 'like', "%{$q}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        // ✅ contadores (sem precisar carregar tudo em memória)
        $total  = CampaignRecipient::where('campaign_id', $campaign->id)->count();
        $valid  = CampaignRecipient::where('campaign_id', $campaign->id)->where('is_valid', 1)->count();
        $invalid = $total - $valid;

        // ✅ View bate com resources/views/campanhas/destinatarios.blade.php
        return view('campanhas.destinatarios', compact('campaign', 'recipients', 'total', 'valid', 'invalid', 'q'));
    }

    public function destroy(Campaign $campaign, CampaignRecipient $recipient)
    {
        // ✅ Segurança: campanha precisa ser do usuário logado
        abort_unless((int)$campaign->user_id === (int)Auth::id(), 403);

        // ✅ Garante que o recipient pertence à campanha
        abort_unless((int)$recipient->campaign_id === (int)$campaign->id, 404);

        $recipient->delete();

        return redirect()
            ->route('campaigns.recipients.index', $campaign->id)
            ->with('success', 'Contato removido da campanha.');
    }

    public function dedup(Campaign $campaign)
    {
        // ✅ Segurança: campanha precisa ser do usuário logado
        abort_unless((int)$campaign->user_id === (int)Auth::id(), 403);

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
            ->route('campaigns.recipients.index', $campaign->id)
            ->with('success', "Deduplicação concluída. Removidos: {$deleted} duplicados.");
    }
}
