<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Chat extends Model
{
    protected $fillable = [
        'user_id',
        'whatsapp_instance_id',
        'remote_jid',
        'title',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(\App\Models\Message::class, 'chat_id');
    }
}
