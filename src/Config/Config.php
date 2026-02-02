<?php
namespace ExcelleInsights\Sage\Config;

use ExcelleInsights\Sage\Support\PackageEnvLoader;

final class Config
{
    private static bool $booted = false;

    private static function boot(): void
    {
        if (!self::$booted) {
            PackageEnvLoader::load();
            self::$booted = true;
        }
    }

    public static function clientId(): string
    {
        self::boot();
        return self::env('QBO_CLIENT_ID');
    }

    public static function clientSecret(): string
    {
        self::boot();
        return self::env('QBO_CLIENT_SECRET');
    }

    public static function companyId(): string
    {
        self::boot();
        return self::env('QBO_COMPANY_ID');
    }

    private static function env(string $key): string
    {
        $value = $_ENV[$key] ?? getenv($key) ?? null;

        if (!$value) {
            throw new \RuntimeException("Missing Sage env variable: {$key}");
        }

        return $value;
    }
}
