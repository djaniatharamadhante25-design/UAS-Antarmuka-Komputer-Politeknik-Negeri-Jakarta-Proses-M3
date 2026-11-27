<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Circuit breaker sederhana. Kalau sebuah API eksternal timeout / tidak bisa
 * dihubungi, tandai "down" selama cooldown (default 60 detik) supaya request
 * berikutnya tidak menunggu timeout lagi.
 */
final class ApiGuard
{
    public static function isDown(string $name): bool
    {
        try {
            return self::store()->has(self::key($name));
        } catch (\Throwable) {
            return false;
        }
    }

    public static function markDown(string $name): void
    {
        try {
            self::store()->put(
                self::key($name),
                now()->timestamp,
                now()->addSeconds((int) config('otaku.api.down_cooldown', 60))
            );
        } catch (\Throwable) {
            // Abaikan jika cache backend tidak tersedia
        }
    }

    private static function store()
    {
        try {
            return Cache::store(config('otaku.cache_store'));
        } catch (\Throwable) {
            return Cache::store('array');
        }
    }

    private static function key(string $name): string
    {
        $prefix = (string) config('otaku.cache_prefix', 'otaku');

        return $prefix.':down:'.preg_replace('/\W/', '', strtolower($name));
    }
}
