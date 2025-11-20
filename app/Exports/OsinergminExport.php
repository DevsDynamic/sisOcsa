<?php

namespace App\Exports;

use App\Models\Osinergmin;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;

class OsinergminExport implements FromQuery, WithHeadings
{
    protected $unit;

    public function __construct($unit = null)
    {
        $this->unit = $unit;
    }

    public function query()
    {
        $query = Osinergmin::query();

        if ($this->unit) {
            $query->where('uuid', $this->unit);
        }

        $fechaInicio = now()->subMonth()->startOfMonth();
        $fechaFin = now()->endOfMonth();

        return $query->whereBetween('response_timestamp', [$fechaInicio, $fechaFin])
                     ->orderBy('id', 'DESC');
    }

    public function headings(): array
    {
        return [
            'ID',
            'UUID',
            'Placa',
            'Evento',
            'Velocidad',
            'Latitud',
            'Longitud',
            'Fecha GPS',
            'Odómetro',
            'Timestamp',
            'Mensaje',
            'Sugerencia',
            'Estado',
            'Creado',
            'Actualizado'
        ];
    }
}