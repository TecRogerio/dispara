<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'chat_id',
        'contact_id',
        'provider_message_id',
        'direction',
        'type',
        'body',
        'status',
        'message_at',
        'raw',
    ];

    protected $casts = [
        'raw' => 'array',
        'message_at' => 'datetime',
    ];

    // Se tua tabela não for "messages", descomenta e ajusta:
    // protected $table = 'messages';

    public function chat(): BelongsTo
    {
        return $this->belongsTo(\App\Models\Chat::class, 'chat_id');
    }
}
