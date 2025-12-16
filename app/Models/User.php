<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * Campos permitidos para mass assignment (User::create)
     */
    protected $fillable = [
        'name',
        'company_name',
        'email',
        'password',
        'api_token',
        'status',
        'daily_limit',
    ];

    /**
     * Campos ocultos
     */
    protected $hidden = [
        'password',
        'remember_token',
        'api_token',
    ];

    /**
     * Casts
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /**
     * Relação: Empresa (User) tem várias instâncias WhatsApp
     */
    public function whatsappInstances()
    {
        return $this->hasMany(\App\Models\WhatsappInstance::class);
    }
}
