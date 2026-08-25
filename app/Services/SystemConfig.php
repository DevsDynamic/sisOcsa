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
        return rtrim(SystemSetting::valueFor("osinergmin_base_url_{$environment}", config("services.osinergmin.base_urls.{$environment}")), '/');
    }

    public static function osinergminToken(string $environment): ?string
    {
        return SystemSetting::valueFor("osinergmin_token_{$environment}", config("services.osinergmin.tokens.{$environment}"));
    }
}
