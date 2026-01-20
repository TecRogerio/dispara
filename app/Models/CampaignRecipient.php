<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Schema;

class CampaignRecipient extends Model
{
    protected $table = 'campaign_recipients';

    protected $fillable = [
        'campaign_id',
        'name',
        'phone_raw',
        'phone_digits',
        'is_valid',

        // compat
        'validation_error',
        'invalid_reason',

        // compat
        'status',
        'send_status',

        'sent_at',
        'delivered_at',
        'read_at',

        // compat
        'fail_reason',
        'last_error',
    ];

    protected $casts = [
        'is_valid' => 'boolean',
        'sent_at' => 'datetime',
        'delivered_at' => 'datetime',
        'read_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }

    // -------------------------------------------------------
    // ✅ COMPAT: status <-> send_status
    // -------------------------------------------------------
    public function getStatusAttribute()
    {
        if (array_key_exists('status', $this->attributes)) {
            return $this->attributes['status'];
        }

        if (array_key_exists('send_status', $this->attributes)) {
            return $this->attributes['send_status'];
        }

        // fallback (caso ainda não tenha carregado attrs)
        if (Schema::hasColumn($this->table, 'status')) return $this->getAttributeFromArray('status');
        if (Schema::hasColumn($this->table, 'send_status')) return $this->getAttributeFromArray('send_status');

        return null;
    }

    public function setStatusAttribute($value): void
    {
        if (Schema::hasColumn($this->table, 'status')) {
            $this->attributes['status'] = $value;
            return;
        }

        if (Schema::hasColumn($this->table, 'send_status')) {
            $this->attributes['send_status'] = $value;
            return;
        }

        // fallback
        $this->attributes['status'] = $value;
    }

    // -------------------------------------------------------
    // ✅ COMPAT: validation_error <-> invalid_reason
    // -------------------------------------------------------
    public function getValidationErrorAttribute()
    {
        if (array_key_exists('validation_error', $this->attributes)) {
            return $this->attributes['validation_error'];
        }

        if (array_key_exists('invalid_reason', $this->attributes)) {
            return $this->attributes['invalid_reason'];
        }

        if (Schema::hasColumn($this->table, 'validation_error')) return $this->getAttributeFromArray('validation_error');
        if (Schema::hasColumn($this->table, 'invalid_reason')) return $this->getAttributeFromArray('invalid_reason');

        return null;
    }

    public function setValidationErrorAttribute($value): void
    {
        if (Schema::hasColumn($this->table, 'validation_error')) {
            $this->attributes['validation_error'] = $value;
            return;
        }

        if (Schema::hasColumn($this->table, 'invalid_reason')) {
            $this->attributes['invalid_reason'] = $value;
            return;
        }

        $this->attributes['validation_error'] = $value;
    }

    // -------------------------------------------------------
    // ✅ COMPAT: fail_reason <-> last_error
    // -------------------------------------------------------
    public function getFailReasonAttribute()
    {
        if (array_key_exists('fail_reason', $this->attributes)) return $this->attributes['fail_reason'];
        if (array_key_exists('last_error', $this->attributes)) return $this->attributes['last_error'];

        if (Schema::hasColumn($this->table, 'fail_reason')) return $this->getAttributeFromArray('fail_reason');
        if (Schema::hasColumn($this->table, 'last_error')) return $this->getAttributeFromArray('last_error');

        return null;
    }

    public function setFailReasonAttribute($value): void
    {
        if (Schema::hasColumn($this->table, 'fail_reason')) {
            $this->attributes['fail_reason'] = $value;
            return;
        }

        if (Schema::hasColumn($this->table, 'last_error')) {
            $this->attributes['last_error'] = $value;
            return;
        }

        $this->attributes['fail_reason'] = $value;
    }
}
