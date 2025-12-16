<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappInstance extends Model
{
    protected $table = 'whatsapp_instances';

    protected $fillable = [
        'user_id',
        'label',
        'instance_name',
        'token',
        'enabled',
        'daily_limit',
        'sent_today',
        'sent_today_date',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'sent_today_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
