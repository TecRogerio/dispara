<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $since = now()->subDay();

        // Compatibilidade: coluna "ativo" pode variar
        $activeCol = null;
        if (Schema::hasColumn('whatsapp_instances', 'is_active')) {
            $activeCol = 'is_active';
        } elseif (Schema::hasColumn('whatsapp_instances', 'active')) {
            $activeCol = 'active';
        }

        // Subquery: último evento por instância (1 linha por instância)
        $lastEventSub = DB::table('whatsapp_instance_events as e1')
            ->select('e1.whatsapp_instance_id', 'e1.event', 'e1.status', 'e1.created_at')
            ->join(DB::raw('(
                SELECT whatsapp_instance_id, MAX(created_at) AS max_created_at
                FROM whatsapp_instance_events
                GROUP BY whatsapp_instance_id
            ) e2'), function ($join) {
                $join->on('e1.whatsapp_instance_id', '=', 'e2.whatsapp_instance_id')
                     ->on('e1.created_at', '=', 'e2.max_created_at');
            });

        // Subquery: erros 24h por instância (1 linha por instância)
        $errors24Sub = DB::table('whatsapp_instance_events')
            ->select('whatsapp_instance_id', DB::raw('COUNT(*) as errors_24h'))
            ->where('event', 'ERROR')
            ->where('created_at', '>=', $since)
            ->groupBy('whatsapp_instance_id');

        // Subquery: stats por instância (1 linha por instância)
        $instancesStatsSub = DB::table('whatsapp_instances as wi')
            ->leftJoinSub($lastEventSub, 'le', function ($join) {
                $join->on('le.whatsapp_instance_id', '=', 'wi.id');
            })
            ->leftJoinSub($errors24Sub, 'e24', function ($join) {
                $join->on('e24.whatsapp_instance_id', '=', 'wi.id');
            })
            ->selectRaw('
                wi.id,
                wi.user_id,
                le.event as last_event,
                le.created_at as last_event_at,
                COALESCE(e24.errors_24h, 0) as errors_24h
            ');

        if ($activeCol) {
            $instancesStatsSub->addSelect(DB::raw("CASE WHEN wi.$activeCol = 1 THEN 1 ELSE 0 END as is_active_flag"));
        } else {
            // se não existir coluna, assume ativo
            $instancesStatsSub->addSelect(DB::raw("1 as is_active_flag"));
        }

        // Agora agrega por usuário (sem inflar por eventos)
        $rows = DB::table('users as u')
            ->leftJoinSub($instancesStatsSub, 's', function ($join) {
                $join->on('s.user_id', '=', 'u.id');
            })
            ->groupBy('u.id', 'u.name', 'u.email')
            ->selectRaw('
                u.id,
                u.name,
                u.email,

                COUNT(s.id) as instances_total,
                COALESCE(SUM(s.is_active_flag), 0) as instances_active,

                COALESCE(SUM(CASE WHEN s.last_event = "CONNECTED" THEN 1 ELSE 0 END), 0) as instances_connected,
                COALESCE(SUM(CASE WHEN s.last_event = "DISCONNECTED" THEN 1 ELSE 0 END), 0) as instances_disconnected,
                COALESCE(SUM(CASE WHEN s.last_event = "QRCODE" THEN 1 ELSE 0 END), 0) as instances_qrcode,

                COALESCE(SUM(s.errors_24h), 0) as errors_24h,

                MAX(s.last_event_at) as last_activity
            ')
            ->orderByDesc('last_activity')
            ->get();

        return view('admin.dashboard', compact('rows'));
    }

    public function userInstances($userId)
    {
        // Subquery: último evento por instância
        $lastEventSub = DB::table('whatsapp_instance_events as e1')
            ->select('e1.whatsapp_instance_id', 'e1.event', 'e1.status', 'e1.created_at', 'e1.message')
            ->join(DB::raw('(
                SELECT whatsapp_instance_id, MAX(created_at) AS max_created_at
                FROM whatsapp_instance_events
                GROUP BY whatsapp_instance_id
            ) e2'), function ($join) {
                $join->on('e1.whatsapp_instance_id', '=', 'e2.whatsapp_instance_id')
                     ->on('e1.created_at', '=', 'e2.max_created_at');
            });

        $instances = DB::table('whatsapp_instances as wi')
            ->where('wi.user_id', $userId)
            ->leftJoinSub($lastEventSub, 'le', function ($join) {
                $join->on('le.whatsapp_instance_id', '=', 'wi.id');
            })
            ->select([
                'wi.*',
                'le.event as last_event',
                'le.status as last_status',
                'le.created_at as last_event_at',
                'le.message as last_message',
            ])
            ->orderByDesc('wi.id')
            ->get();

        $user = DB::table('users')->where('id', $userId)->first();

        return view('admin.user_instances', compact('user', 'instances'));
    }

    public function instanceEvents($instanceId)
    {
        $instance = DB::table('whatsapp_instances')->where('id', $instanceId)->first();

        $events = DB::table('whatsapp_instance_events')
            ->where('whatsapp_instance_id', $instanceId)
            ->orderByDesc('id')
            ->limit(200)
            ->get();

        return view('admin.instance_events', compact('instance', 'events'));
    }
}
