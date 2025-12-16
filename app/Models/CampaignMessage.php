<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CampaignMessage extends Model
{
    protected $table = 'campaign_messages';

    protected $fillable = [
        'campaign_id',
        'position',
        'primary_type',
        'text',
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
