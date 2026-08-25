<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Mail;

class DynamicMailConfig
{
    public static function values(): array
    {
        return [
            'mail_host' => SystemSetting::valueFor('mail_host', config('mail.mailers.smtp.host')),
            'mail_port' => SystemSetting::valueFor('mail_port', config('mail.mailers.smtp.port', 587)),
            'mail_username' => SystemSetting::valueFor('mail_username', config('mail.mailers.smtp.username')),
            'mail_password' => SystemSetting::valueFor('mail_password', config('mail.mailers.smtp.password')),
            'mail_encryption' => SystemSetting::valueFor('mail_encryption', config('mail.mailers.smtp.encryption', 'tls')),
            'mail_from_address' => SystemSetting::valueFor('mail_from_address', config('mail.from.address')),
            'mail_from_name' => SystemSetting::valueFor('mail_from_name', config('mail.from.name', config('app.name'))),
            'mail_alert_recipients' => SystemSetting::valueFor('mail_alert_recipients', ''),
        ];
    }

    public static function apply(array $overrides = []): array
    {
        $values = array_merge(static::values(), $overrides);
        $encryption = blank($values['mail_encryption'] ?? null) ? null : $values['mail_encryption'];

        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => $values['mail_host'],
            'mail.mailers.smtp.port' => (int) $values['mail_port'],
            'mail.mailers.smtp.username' => $values['mail_username'] ?: null,
            'mail.mailers.smtp.password' => $values['mail_password'] ?: null,
            'mail.mailers.smtp.encryption' => $encryption,
            'mail.mailers.smtp.scheme' => $encryption === 'ssl' ? 'smtps' : null,
            'mail.mailers.smtp.timeout' => 10,
            'mail.from.address' => $values['mail_from_address'],
            'mail.from.name' => $values['mail_from_name'],
        ]);

        Mail::purge('smtp');

        return $values;
    }
}
