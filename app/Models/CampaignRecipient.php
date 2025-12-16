<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignRecipient extends Model
{
    protected $table = 'campaign_recipients';

    protected $fillable = [
        'campaign_id',
        'name',
        'phone_raw',
        'phone_digits',
        'is_valid',
        'validation_error',
        'status',
        'sent_at',
        'delivered_at',
        'fail_reason',
    ];

    protected $casts = [
        'is_valid' => 'boolean',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }
}
