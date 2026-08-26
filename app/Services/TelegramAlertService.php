<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class TelegramAlertService
{
    public static function values(): array
    {
        return [
            'telegram_enabled' => filter_var(SystemSetting::valueFor('telegram_enabled', false), FILTER_VALIDATE_BOOL),
            'telegram_bot_token' => SystemSetting::valueFor('telegram_bot_token'),
            'telegram_chat_ids' => SystemSetting::valueFor('telegram_chat_ids', ''),
        ];
    }

    public static function chatIds(?string $value = null): array
    {
        $value ??= (string) static::values()['telegram_chat_ids'];

        return array_values(array_unique(array_filter(array_map(
            'trim',
            preg_split('/[,;\r\n]+/', $value)
        ))));
    }

    public static function send(string $message, ?string $token = null, ?array $chatIds = null): void
    {
        $values = static::values();
        $token = filled($token) ? $token : $values['telegram_bot_token'];
        $chatIds ??= static::chatIds();

        if (blank($token) || empty($chatIds)) {
            throw new RuntimeException('Configura el token del bot y al menos un Chat ID.');
        }

        foreach ($chatIds as $chatId) {
            $response = static::client()->post("https://api.telegram.org/bot{$token}/sendMessage", [
                'chat_id' => $chatId,
                'text' => $message,
                'parse_mode' => 'HTML',
                'disable_web_page_preview' => true,
            ]);

            if (! $response->successful() || ! $response->json('ok')) {
                throw new RuntimeException(
                    'Telegram rechazó el envío al Chat ID '.$chatId.': '.
                    mb_substr((string) ($response->json('description') ?: "HTTP {$response->status()}"), 0, 250)
                );
            }
        }
    }

    private static function client(): PendingRequest
    {
        return Http::connectTimeout(5)->timeout(10)->acceptJson();
    }
}
