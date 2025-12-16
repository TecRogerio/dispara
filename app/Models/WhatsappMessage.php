<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappMessage extends Model
{
    protected $table = 'whatsapp_messages';

    protected $fillable = [
        'user_id',
        'whatsapp_instance_id',
        'to',
        'message',
        'status',
        'http_status',
        'response_json',
    ];

    public function instance()
    {
        return $this->belongsTo(\App\Models\WhatsappInstance::class, 'whatsapp_instance_id');
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }
}
