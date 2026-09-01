<?php

namespace App\Services;

use Carbon\Carbon;
use Carbon\CarbonInterface;

class UnitOperationalStatus
{
    public const HEALTHY_MINUTES = 5;
    public const ALERT_MINUTES = 10;
    public const GPS_FRESH_MINUTES = 10;

    public function evaluate(mixed $sourceUpdatedAt, ?CarbonInterface $lastTransmissionAt, ?string $responseStatus): array
    {
        $sourceAt = $this->parseDate($sourceUpdatedAt);
        $transmissionAt = $lastTransmissionAt ? Carbon::instance($lastTransmissionAt) : null;
        $transmissionAge = $this->ageInMinutes($transmissionAt);
        $sourceAge = $this->ageInMinutes($sourceAt);
        $status = strtoupper(trim((string) $responseStatus));

        $result = [
            'tone' => 'unknown',
            'label' => 'Sin historial',
            'detail' => 'Todavía no hay envíos registrados para esta unidad.',
            'last_transmission_at' => $transmissionAt?->toIso8601String(),
            'source_updated_at' => $sourceAt?->toIso8601String(),
            'response_status' => $status ?: null,
        ];

        if (! $transmissionAt) {
            return $result;
        }

        if ($transmissionAge > self::ALERT_MINUTES) {
            return array_merge($result, [
                'tone' => 'danger',
                'label' => 'Sin transmisión',
                'detail' => "El último intento fue hace {$transmissionAge} min.",
            ]);
        }

        if (in_array($status, ['ERROR', 'REJECTED', 'FAILED'], true)) {
            return array_merge($result, [
                'tone' => 'danger',
                'label' => 'Envío rechazado',
                'detail' => 'La transmisión está activa, pero el último resultado fue rechazado.',
            ]);
        }

        if (! in_array($status, ['SUCCESS', 'CREATED', 'ACCEPTED', 'OK'], true)) {
            return array_merge($result, [
                'tone' => 'warning',
                'label' => 'Sin confirmación',
                'detail' => 'Hay un envío reciente, pero Osinergmin no confirmó su aceptación.',
            ]);
        }

        if (! $sourceAt) {
            return array_merge($result, [
                'tone' => 'warning',
                'label' => 'GPS sin fecha',
                'detail' => 'Osinergmin acepta los envíos, pero OCSA no informa la fecha del dato GPS.',
            ]);
        }

        if ($sourceAge > self::GPS_FRESH_MINUTES) {
            return array_merge($result, [
                'tone' => 'warning',
                'label' => 'GPS desactualizado',
                'detail' => "Se transmite y Osinergmin acepta, pero el dato de OCSA tiene {$sourceAge} min de antigüedad.",
            ]);
        }

        if ($transmissionAge > self::HEALTHY_MINUTES) {
            return array_merge($result, [
                'tone' => 'warning',
                'label' => 'Envío demorado',
                'detail' => "El último envío aceptado fue hace {$transmissionAge} min.",
            ]);
        }

        return array_merge($result, [
            'tone' => 'success',
            'label' => 'Operativo',
            'detail' => 'OCSA entrega datos recientes y Osinergmin esta aceptando los envios.',
        ]);
    }

    private function parseDate(mixed $value): ?Carbon
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            if (is_numeric($value)) {
                $timestamp = (int) $value;
                if ($timestamp > 9999999999) {
                    $timestamp = (int) floor($timestamp / 1000);
                }

                return Carbon::createFromTimestamp($timestamp, config('app.timezone'));
            }

            return Carbon::parse((string) $value, config('app.timezone'));
        } catch (\Throwable) {
            return null;
        }
    }

    private function ageInMinutes(?CarbonInterface $date): ?int
    {
        if (! $date) {
            return null;
        }

        return $date->isFuture() ? 0 : (int) floor($date->diffInSeconds(now()) / 60);
    }
}
