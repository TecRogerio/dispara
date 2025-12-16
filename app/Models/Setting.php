<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';

    protected $fillable = [
        'tenant_id',
        'key',
        'value',
    ];

    /**
     * Pega uma config por tenant com fallback.
     */
    public static function getForTenant(int $tenantId, string $key, $default = null): mixed
    {
        $val = static::where('tenant_id', $tenantId)->where('key', $key)->value('value');

        // fallback pro tenant 1 (global)
        if ($val === null && $tenantId !== 1) {
            $val = static::where('tenant_id', 1)->where('key', $key)->value('value');
        }

        return $val === null ? $default : $val;
    }

    /**
     * Converte pra int com fallback.
     */
    public static function intForTenant(int $tenantId, string $key, int $default): int
    {
        $val = static::getForTenant($tenantId, $key, null);
        if ($val === null) return $default;

        $n = filter_var($val, FILTER_VALIDATE_INT);
        return ($n === false) ? $default : (int) $n;
    }

    /**
     * Converte pra bool com fallback.
     */
    public static function boolForTenant(int $tenantId, string $key, bool $default): bool
    {
        $val = static::getForTenant($tenantId, $key, null);
        if ($val === null) return $default;

        $v = strtolower(trim((string)$val));
        if (in_array($v, ['1','true','yes','y','on'], true)) return true;
        if (in_array($v, ['0','false','no','n','off'], true)) return false;

        return $default;
    }
}
