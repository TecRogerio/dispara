<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    protected $table = 'campaigns';

    protected $fillable = [
        'user_id',
        'whatsapp_instance_id',
        'name',

        // banco (conforme DESCRIBE campaigns)
        'delay_min_seconds',
        'delay_max_seconds',
        'burst_max',
        'burst_pause_seconds',
        'daily_limit_override',
        'status',

        'total_recipients',
        'valid_recipients',
        'invalid_recipients',

        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'delay_min_seconds' => 'integer',
        'delay_max_seconds' => 'integer',
        'burst_max' => 'integer',
        'burst_pause_seconds' => 'integer',
        'daily_limit_override' => 'integer',

        'total_recipients' => 'integer',
        'valid_recipients' => 'integer',
        'invalid_recipients' => 'integer',

        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function instance()
    {
        return $this->belongsTo(WhatsappInstance::class, 'whatsapp_instance_id');
    }

    public function recipients()
    {
        return $this->hasMany(CampaignRecipient::class, 'campaign_id');
    }

    public function messages()
    {
        // Agora que vamos garantir a coluna "position", pode ordenar sem quebrar
        return $this->hasMany(CampaignMessage::class, 'campaign_id')->orderBy('position');
    }
}
