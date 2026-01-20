<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignMessage extends Model
{
    protected $table = 'campaign_messages';

    protected $fillable = [
        'campaign_id',
        'position',

        // tipo principal: text | image | video | document | audio
        'primary_type',

        // texto
        'text',

        // mídia (novo padrão)
        'caption',
        'media_url',
        'media_path',
        'media_type',
        'media_mime',
        'media_filename',
        'media_size',

        // mídia (padrão alternativo/antigo)
        'mime_type',
        'file_name',
        'file_size',

        // antigos
        'location_lat',
        'location_lng',
        'location_name',
        'location_address',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }
}
