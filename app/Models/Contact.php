<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected $table = 'contacts';

    protected $fillable = [
        'tenant_id',
        'name',
        'pushname',
        'phone_e164',
        'phone_raw',
        'profile_pic_url',
        'email',
        'is_group',
        'metadata',
    ];

    protected $casts = [
        'is_group' => 'boolean',
        'metadata' => 'array',
    ];
}
