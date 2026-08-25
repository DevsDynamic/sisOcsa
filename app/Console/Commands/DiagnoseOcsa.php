<?php

namespace App\Console\Commands;

use App\Models\Person;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use App\Services\SystemConfig;

class DiagnoseOcsa extends Command
{
    protected $signature = 'ocsa:diagnose';

    protected $description = 'Comprueba los tokens GPS contra OCSA sin retransmitir ni guardar datos';

    public function handle(): int
    {
        $sources = Person::activeGpsSources()->get();
        $url = SystemConfig::ocsaBaseUrl() . config('services.ocsa.paths.units');

        $this->info('Endpoint OCSA: ' . $url);
        $this->line('Fuentes GPS activas: ' . $sources->count());

        if ($sources->isEmpty()) {
            $this->error('No hay contactos activos con token GPS.');
            return self::FAILURE;
        }

        $client = new Client(['timeout' => 20, 'connect_timeout' => 10, 'http_errors' => false]);
        $failed = false;

        foreach ($sources as $source) {
            try {
                $response = $client->get($url, [
                    'query' => [
                        'key' => $source->token,
                        'include' => ['ignition', 'battery_voltage', 'supply_voltage'],
                    ],
                ]);
                $body = json_decode((string) $response->getBody(), true);
                $units = $body['data']['units'] ?? [];
                $providerError = $body['error']['msg'] ?? null;
                $maskedToken = substr($source->token, 0, 4) . '…' . substr($source->token, -4);

                if ($response->getStatusCode() !== 200 || $providerError) {
                    $failed = true;
                    $this->error("{$source->full_name} [{$maskedToken}]: HTTP {$response->getStatusCode()} - " . ($providerError ?: 'respuesta inesperada'));
                    continue;
                }

                $this->info("{$source->full_name} [{$maskedToken}]: " . count($units) . ' unidades recibidas.');
            } catch (\Throwable $exception) {
                $failed = true;
                $this->error("{$source->full_name}: " . $exception->getMessage());
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
