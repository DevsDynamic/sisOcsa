<?php

namespace App\Services;

use App\Models\SystemSetting;

class SystemConfig
{
    public static function environment(): string
    {
        return SystemSetting::valueFor('osinergmin_environment', config('services.osinergmin.environment'));
    }

    public static function ocsaBaseUrl(): string
    {
        return rtrim(SystemSetting::valueFor('ocsa_base_url', config('services.ocsa.base_url')), '/');
    }

    public static function osinergminBaseUrl(string $environment): string
    {
        $url = rtrim(SystemSetting::valueFor("osinergmin_base_url_{$environment}", config("services.osinergmin.base_urls.{$environment}")), '/');

        // La pantalla solicita solo el host. Si se pegó un endpoint completo,
        // retiramos la ruta conocida para evitar duplicarla al enviar.
        return preg_replace(
            '#/api-gps-ingesta(?:-batch)?(?:/api/v1/(?:trama|trama-batch))?/?$#i',
            '',
            $url
        );
    }

    public static function osinergminEndpoint(string $environment, string $type): string
    {
        return static::osinergminBaseUrl($environment).config("services.osinergmin.paths.{$type}");
    }

    public static function osinergminToken(string $environment): ?string
    {
        return SystemSetting::valueFor("osinergmin_token_{$environment}", config("services.osinergmin.tokens.{$environment}"));
    }
}
