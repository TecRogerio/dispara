<?php

namespace App\Http\Controllers;

use App\Models\WhatsappInstance;
use App\Models\WhatsappMessage;
use App\Services\EvolutionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function create()
    {
        $user = Auth::user();

        // pega instâncias da empresa
        $instances = WhatsappInstance::where('user_id', $user->id)
            ->orderBy('label')
            ->get();

        return view('mensagens.enviar', compact('instances'));
    }

    public function store(Request $request, EvolutionService $evo)
    {
        $user = Auth::user();

        $data = $request->validate([
            'whatsapp_instance_id' => ['required', 'integer'],
            'to' => ['required', 'string', 'max:40'],
            'message' => ['required', 'string', 'max:2000'],
            'auto55' => ['nullable', 'string'], // checkbox
        ]);

        $instance = WhatsappInstance::where('user_id', $user->id)
            ->where('id', (int)$data['whatsapp_instance_id'])
            ->firstOrFail();

        // Normaliza telefone: só dígitos
        $to = preg_replace('/\D+/', '', $data['to'] ?? '');

        // auto +55 (DDI BR)
        $auto55 = isset($data['auto55']) && $data['auto55'] === '1';

        if ($auto55) {
            if (strpos($to, '55') !== 0) {
                $to = '55' . $to;
            }
        }

        // Validação básica: precisa ficar com algo minimamente plausível
        if (strlen($to) < 10) {
            return back()->withInput()->with('error', 'Telefone inválido (ficou muito curto após limpar).');
        }

        // --- Limite diário por instância ---
        $today = date('Y-m-d');

        // reset contador se virou o dia
        if (!empty($instance->sent_today_date) && (string)$instance->sent_today_date !== $today) {
            $instance->sent_today = 0;
            $instance->sent_today_date = $today;
            $instance->save();
        }

        if (empty($instance->sent_today_date)) {
            $instance->sent_today_date = $today;
            $instance->save();
        }

        $limit = (int)($instance->daily_limit ?? 200);
        $sentToday = (int)($instance->sent_today ?? 0);

        if ($sentToday >= $limit) {
            return back()->withInput()->with('error', "Limite diário atingido para esta instância ({$limit}/dia).");
        }

        // Cria log (queued)
        $log = WhatsappMessage::create([
            'user_id' => $user->id,
            'whatsapp_instance_id' => $instance->id,
            'to' => $to,
            'message' => $data['message'],
            'status' => 'queued',
        ]);

        // envia
        $resp = $evo->sendText($instance->instance_name, $to, $data['message']);

        $log->http_status = $resp['http_status'] ?? null;
        $log->response_json = json_encode($resp, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        if (!($resp['ok'] ?? false)) {
            $log->status = 'failed';
            $log->save();

            return back()->withInput()->with('error', 'Falha ao enviar. Veja o debug (na tabela de logs depois).');
        }

        $log->status = 'sent';
        $log->save();

        // incrementa contador
        $instance->sent_today = $sentToday + 1;
        $instance->sent_today_date = $today;
        $instance->save();

        return redirect()->route('mensagens.enviar')->with('success', 'Mensagem enviada (ou aceita pela Evolution).');
    }
}
