<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignAttachment extends Model
{
    protected $table = 'campaign_attachments';

    protected $fillable = [
        'campaign_message_id',
        'kind',
        'original_name',
        'mime_type',
        'size_bytes',
        'storage_disk',
        'storage_path',
        'public_url',
    ];

    public function message()
    {
        return $this->belongsTo(CampaignMessage::class, 'campaign_message_id');
    }
}
