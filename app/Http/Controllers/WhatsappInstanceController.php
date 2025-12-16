<?php

namespace App\Http\Controllers;

use App\Models\WhatsappInstance;
use App\Services\EvolutionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WhatsappInstanceController extends Controller
{
    /**
     * Lista instâncias da empresa logada
     */
    public function index()
    {
        $user = Auth::user();

        $instances = WhatsappInstance::where('user_id', $user->id)
            ->orderByDesc('id')
            ->get();

        return view('instancias.index', compact('instances'));
    }

    /**
     * Form de nova instância
     */
    public function create()
    {
        $user = Auth::user();

        $count = WhatsappInstance::where('user_id', $user->id)->count();
        if ($count >= 3) {
            return redirect()->route('instancias.index')
                ->with('error', 'Limite atingido: sua empresa pode conectar no máximo 3 WhatsApps.');
        }

        return view('instancias.create');
    }

    /**
     * Salva nova instância:
     * 1) valida
     * 2) cria na Evolution
     * 3) salva no banco
     */
    public function store(Request $request, EvolutionService $evo)
    {
        $user = Auth::user();

        // regra: max 3 instâncias por empresa
        $count = WhatsappInstance::where('user_id', $user->id)->count();
        if ($count >= 3) {
            return back()->withInput()->withErrors([
                'instance_name' => 'Limite atingido: sua empresa pode conectar no máximo 3 WhatsApps.',
            ]);
        }

        $data = $request->validate([
            'label' => ['required', 'string', 'max:120'],
            'instance_name' => ['required', 'string', 'max:120', 'regex:/^[a-zA-Z0-9._-]+$/'],
            'daily_limit' => ['required', 'integer', 'min:1', 'max:200'],
        ], [
            'instance_name.regex' => 'Instance name inválido. Use apenas letras, números, ponto, hífen (-) ou underscore (_), sem espaços.',
        ]);

        // evita duplicar instance_name na mesma empresa
        $exists = WhatsappInstance::where('user_id', $user->id)
            ->where('instance_name', $data['instance_name'])
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors([
                'instance_name' => 'Essa instância já está cadastrada para sua empresa.',
            ]);
        }

        // token forte por instância (guarda no banco)
        $token = Str::random(64);

        // 1) Cria no Evolution primeiro
        $create = $evo->createInstance($data['instance_name'], $token, true);

        if (!($create['ok'] ?? false)) {
            $msg = $create['message'] ?? $create['error'] ?? 'Falha ao criar instância na Evolution.';
            return back()->withInput()->withErrors([
                'instance_name' => $msg,
            ]);
        }

        // 2) Salva no banco local (colunas reais da tua tabela)
        WhatsappInstance::create([
            'user_id'         => $user->id,
            'label'           => $data['label'],
            'instance_name'   => $data['instance_name'],
            'token'           => $token,
            'enabled'         => true,
            'daily_limit'     => (int) $data['daily_limit'],
            'sent_today'      => 0,
            'sent_today_date' => null,
        ]);

        return redirect()->route('instancias.index')
            ->with('success', 'Instância criada e salva. Agora clique em "Conectar" para gerar o QR.');
    }

    /**
     * Conecta instância (gera QR / inicia sessão)
     * Se já estiver "open", volta pra lista automaticamente.
     */
    public function connect($id, EvolutionService $evo)
    {
        $user = Auth::user();

        $instance = WhatsappInstance::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        // usa token por instância (importante)
        $resp = $evo->connect($instance->instance_name, $instance->token);

        if (!($resp['ok'] ?? false)) {
            $msg = $resp['message'] ?? $resp['error'] ?? 'Falha ao conectar/gerar QR na Evolution.';
            return redirect()->route('instancias.index')->with('error', $msg);
        }

        // Se a Evolution indicar state=open, já está conectado -> volta pra lista
        $state = $resp['body']['instance']['state'] ?? $resp['body']['state'] ?? null;
        if (is_string($state) && strtolower($state) === 'open') {
            return redirect()->route('instancias.index')
                ->with('success', 'Instância conectada com sucesso ✅');
        }

        // Se retornou QR, mostra a página do QR
        $qrDataUri = $resp['qr_data_uri'] ?? null;
        if (is_string($qrDataUri) && $qrDataUri !== '') {
            return view('instancias.qrcode', [
                'instanceId'   => $instance->id,
                'instanceName' => $instance->instance_name,
                'data'         => $resp,
                'error'        => null,
            ]);
        }

        // Caso não venha QR e não venha open, volta com info
        return redirect()->route('instancias.index')
            ->with('info', 'A Evolution respondeu, mas não retornou QR. Verifique no Manager se a instância já está conectada.');
    }

    /**
     * Ativar / Desativar instância no seu sistema (não remove na Evolution)
     * (coluna correta: enabled)
     */
    public function toggle($id)
    {
        $user = Auth::user();

        $instance = WhatsappInstance::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        $instance->enabled = !((bool) $instance->enabled);
        $instance->save();

        return redirect()->route('instancias.index')
            ->with('success', 'Status da instância atualizado.');
    }

    /**
     * Remove instância do seu sistema (por enquanto não deleta na Evolution)
     */
    public function destroy($id)
    {
        $user = Auth::user();

        $instance = WhatsappInstance::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        $instance->delete();

        return redirect()->route('instancias.index')
            ->with('success', 'Instância removida do sistema.');
    }
}
