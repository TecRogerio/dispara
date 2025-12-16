<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappInstanceEvent extends Model
{
    protected $table = 'whatsapp_instance_events';

    protected $fillable = [
        'tenant_id',
        'whatsapp_instance_id',
        'event',
        'source',
        'status',
        'phone',
        'ip',
        'user_agent',
        'payload',
        'message',
    ];

    protected $casts = [
        'payload' => 'array',
    ];
}
