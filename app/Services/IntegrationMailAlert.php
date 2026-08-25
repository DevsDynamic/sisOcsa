<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class IntegrationMailAlert
{
    public function handle(string $environment, string $stage, string $status, string $message): void
    {
        $recipients = $this->recipients();
        if ($recipients === []) {
            return;
        }

        $incidentKey = "integration:mail-incident:{$environment}";

        if ($status === 'ERROR') {
            Cache::forever($incidentKey, true);
            $cooldownKey = "integration:mail-alert-cooldown:{$environment}";
            if (! Cache::add($cooldownKey, true, now()->addMinutes(30))) {
                return;
            }

            $this->send(
                $recipients,
                "Alerta de integración ({$environment})",
                "Se detectó un error en la integración.\n\nEtapa: {$stage}\nAmbiente: {$environment}\nFecha: ".now()->format('d/m/Y H:i:s')."\nDetalle: {$message}"
            );
            return;
        }

        if ($stage === 'RUN' && $status === 'SUCCESS' && Cache::pull($incidentKey)) {
            $this->send(
                $recipients,
                "Integración recuperada ({$environment})",
                "La integración volvió a finalizar correctamente.\n\nAmbiente: {$environment}\nFecha: ".now()->format('d/m/Y H:i:s')."\nDetalle: {$message}"
            );
        }
    }

    private function recipients(): array
    {
        $value = DynamicMailConfig::values()['mail_alert_recipients'];

        return array_values(array_unique(array_filter(
            array_map('trim', preg_split('/[,;\r\n]+/', (string) $value)),
            fn ($email) => filter_var($email, FILTER_VALIDATE_EMAIL)
        )));
    }

    private function send(array $recipients, string $subject, string $body): void
    {
        try {
            $values = DynamicMailConfig::apply();
            Mail::mailer('smtp')->raw($body, function ($mail) use ($recipients, $subject, $values) {
                $mail->to($recipients)
                    ->from($values['mail_from_address'], $values['mail_from_name'])
                    ->subject($subject);
            });
        } catch (Throwable $exception) {
            Log::error('No se pudo enviar la alerta de integración por correo.', [
                'exception' => $exception->getMessage(),
            ]);
        }
    }
}
