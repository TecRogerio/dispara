<?php

namespace App\Support;

use App\Models\Setting;

class Settings
{
    /**
     * Cache simples em memória (por request).
     * Evita múltiplos SELECTs repetidos durante um dispatch grande.
     */
    private static array $cache = [];

    public static function tenantId(): int
    {
        try {
            $u = auth()->user();
            if ($u && isset($u->tenant_id) && (int) $u->tenant_id > 0) {
                return (int) $u->tenant_id;
            }
        } catch (\Throwable $e) {
            // ignore
        }

        return 1;
    }

    /**
     * Lê uma config por tenant.
     */
    public static function get(string $key, $default = null): mixed
    {
        $tenantId = self::tenantId();
        $ck = $tenantId . '|' . $key;

        if (array_key_exists($ck, self::$cache)) {
            return self::$cache[$ck];
        }

        try {
            $val = Setting::where('tenant_id', $tenantId)
                ->where('key', $key)
                ->value('value');
        } catch (\Throwable $e) {
            $val = null;
        }

        if ($val === null) {
            self::$cache[$ck] = $default;
            return $default;
        }

        self::$cache[$ck] = $val;
        return $val;
    }

    public static function int(string $key, int $default): int
    {
        $v = self::get($key, $default);

        // aceita "12", 12, "12.0"
        if (is_numeric($v)) {
            return (int) $v;
        }

        return $default;
    }

    public static function bool(string $key, bool $default): bool
    {
        $v = self::get($key, $default ? '1' : '0');

        if (is_bool($v)) return $v;

        $v = strtolower(trim((string) $v));

        if (in_array($v, ['1','true','yes','y','on'], true)) return true;
        if (in_array($v, ['0','false','no','n','off'], true)) return false;

        return $default;
    }

    /**
     * (Opcional) Limpa cache - útil em testes.
     */
    public static function flush(): void
    {
        self::$cache = [];
    }
}
