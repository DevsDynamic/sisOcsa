<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;

class IntegrationTelegramAlert
{
    public function handle(string $environment, string $stage, string $status, string $message): void
    {
        $values = TelegramAlertService::values();
        if (! $values['telegram_enabled'] || blank($values['telegram_bot_token']) || TelegramAlertService::chatIds() === []) {
            return;
        }

        $incidentKey = "integration:telegram-incident:{$environment}";

        if ($status === 'ERROR') {
            Cache::forever($incidentKey, true);
            if (! Cache::add("integration:telegram-alert-cooldown:{$environment}", true, now()->addMinutes(30))) {
                return;
            }

            $this->send(
                '🔴 <b>Error de integración</b>'."\n".
                '<b>Etapa:</b> '.e($stage)."\n".
                '<b>Ambiente:</b> '.e(strtoupper($environment))."\n".
                '<b>Fecha:</b> '.now()->format('d/m/Y H:i:s')."\n".
                '<b>Detalle:</b> '.e(mb_substr($message, 0, 1500))
            );
            return;
        }

        if ($stage === 'RUN' && $status === 'SUCCESS' && Cache::pull($incidentKey)) {
            $this->send(
                '✅ <b>Integración recuperada</b>'."\n".
                '<b>Ambiente:</b> '.e(strtoupper($environment))."\n".
                '<b>Fecha:</b> '.now()->format('d/m/Y H:i:s')."\n".
                '<b>Detalle:</b> '.e(mb_substr($message, 0, 1500))
            );
        }
    }

    private function send(string $message): void
    {
        try {
            TelegramAlertService::send($message);
        } catch (Throwable $exception) {
            Log::error('No se pudo enviar la alerta de integración por Telegram.', [
                'exception' => $exception->getMessage(),
            ]);
        }
    }
}
