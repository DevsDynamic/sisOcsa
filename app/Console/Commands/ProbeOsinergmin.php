<?php

namespace App\Console\Commands;

use App\Services\SystemConfig;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ProbeOsinergmin extends Command
{
    protected $signature = 'osinergmin:probe
        {--environment= : production o development; por defecto usa el ambiente activo}
        {--type=unit : unit o batch}
        {--plate=TEST-001 : Placa de prueba}
        {--uuid=OSIN-TEST-001 : Identificador de la unidad}
        {--event=acc_on : Evento GPS}
        {--speed=0 : Velocidad}
        {--latitude=-12.046374 : Latitud}
        {--longitude=-77.042793 : Longitud}
        {--altitude=169 : Altitud}
        {--odometer=1000 : Odometro en kilometros}
        {--send : Ejecuta realmente el POST; sin esta opcion solo muestra la solicitud}';

    protected $description = 'Muestra y, opcionalmente, prueba una trama plana contra PMGO sin guardarla en la base de datos';

    public function handle(): int
    {
        $environment = $this->option('environment') ?: SystemConfig::environment();
        $type = strtolower((string) $this->option('type'));

        if (! in_array($environment, ['production', 'development'], true)) {
            $this->error('El ambiente debe ser production o development.');
            return self::INVALID;
        }

        if (! in_array($type, ['unit', 'batch'], true)) {
            $this->error('El tipo debe ser unit o batch.');
            return self::INVALID;
        }

        $token = SystemConfig::osinergminToken($environment);
        if (blank($token)) {
            $this->error("No existe un token Osinergmin configurado para {$environment}.");
            return self::FAILURE;
        }

        $unit = [
            'event' => (string) $this->option('event'),
            'plate' => strtoupper((string) $this->option('plate')),
            'speed' => (float) $this->option('speed'),
            'position' => [
                'latitude' => (float) $this->option('latitude'),
                'longitude' => (float) $this->option('longitude'),
                'altitude' => (float) $this->option('altitude'),
            ],
            'gpsDate' => Carbon::now('UTC')->format('Y-m-d\TH:i:s.v\Z'),
            'tokenTrama' => $token,
            'odometer' => (int) $this->option('odometer'),
            'uuid' => (string) $this->option('uuid'),
        ];

        $payload = $type === 'batch' ? [$unit] : $unit;
        $endpoint = SystemConfig::osinergminEndpoint($environment, $type);
        $maskedPayload = $payload;
        if ($type === 'batch') {
            $maskedPayload[0]['tokenTrama'] = $this->mask($token);
        } else {
            $maskedPayload['tokenTrama'] = $this->mask($token);
        }

        $this->newLine();
        $this->info('SOLICITUD PMGO');
        $this->table(['Dato', 'Valor'], [
            ['Ambiente', $environment],
            ['Tipo', $type],
            ['Metodo', 'POST'],
            ['Endpoint', $endpoint],
            ['Content-Type', 'application/json'],
            ['Accept', 'application/json'],
            ['Token configurado', $this->mask($token).' ('.strlen($token).' caracteres)'],
            ['Fecha UTC', $unit['gpsDate']],
        ]);
        $this->line(json_encode($maskedPayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        if (! $this->option('send')) {
            $this->warn('MODO SEGURO: no se envio nada. Agrega --send para ejecutar el POST real.');
            return self::SUCCESS;
        }

        $client = new Client([
            'connect_timeout' => 15,
            'timeout' => 40,
            'http_errors' => false,
        ]);

        try {
            $publicIp = trim((string) $client->get('https://api.ipify.org')->getBody());
        } catch (\Throwable $exception) {
            $publicIp = 'No se pudo determinar: '.$exception->getMessage();
        }

        $startedAt = microtime(true);
        try {
            $response = $client->post($endpoint, [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
                'body' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
            ]);
            $elapsed = round((microtime(true) - $startedAt) * 1000);
            $body = (string) $response->getBody();
            preg_match('/support\s+id\s+is\s*:\s*([0-9]+)/i', strip_tags($body), $supportMatch);

            $this->newLine();
            $this->info('RESPUESTA PMGO');
            $this->table(['Dato', 'Valor'], [
                ['IP publica de salida', $publicIp],
                ['HTTP', (string) $response->getStatusCode()],
                ['Content-Type', $response->getHeaderLine('Content-Type') ?: '(sin cabecera)'],
                ['Duracion', $elapsed.' ms'],
                ['Support ID', $supportMatch[1] ?? '(no informado)'],
            ]);
            $this->line($this->prettyBody($body));

            $isJson = Str::contains(strtolower($response->getHeaderLine('Content-Type')), 'json');
            $isSuccess = $response->getStatusCode() >= 200
                && $response->getStatusCode() < 300
                && $isJson;

            if (! $isSuccess) {
                $this->error('La respuesta no fue una confirmacion JSON valida del API.');
                return self::FAILURE;
            }

            $this->info('PMGO devolvio una respuesta JSON HTTP exitosa. Revisa el estado funcional dentro del cuerpo.');
            return self::SUCCESS;
        } catch (\Throwable $exception) {
            $elapsed = round((microtime(true) - $startedAt) * 1000);
            $this->error("Fallo de conexion despues de {$elapsed} ms: {$exception->getMessage()}");
            $this->line('IP publica de salida: '.$publicIp);
            return self::FAILURE;
        }
    }

    private function mask(string $value): string
    {
        return strlen($value) <= 8
            ? str_repeat('*', strlen($value))
            : substr($value, 0, 4).'...'.substr($value, -4);
    }

    private function prettyBody(string $body): string
    {
        $decoded = json_decode($body, true);

        return is_array($decoded)
            ? json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
            : $body;
    }
}
