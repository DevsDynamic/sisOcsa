<?php

namespace App\Exports;

use App\Models\Osinergmin;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Carbon\Carbon;

class OsinergminExport implements FromQuery, WithHeadings
// {
//     protected $unit;

//     public function __construct($unit = null)
//     {
//         $this->unit = $unit;
//     }

//     public function query()
//     {
//         $query = Osinergmin::query();

//         if ($this->unit) {
//             $query->where('uuid', $this->unit);
//         }

//         $fechaInicio = now()->subMonth()->startOfMonth();
//         $fechaFin = now()->endOfMonth();

//         return $query->whereBetween('response_timestamp', [$fechaInicio, $fechaFin])
//                      ->orderBy('id', 'DESC');
//     }

//     public function headings(): array
//     {
//         return [
//             'ID','UUID','Placa','Evento','Velocidad','Latitud','Longitud',
//             'Fecha GPS','Odómetro','Timestamp','Mensaje','Sugerencia','Estado','Creado','Actualizado'
//         ];
//     }
// }
{
    protected $unit;
    protected $from;
    protected $to;
    protected $status;

    public function __construct($unit = null, $from = null, $to = null, $status = null)
    {
        $this->unit = $unit;
        $this->from = $from;
        $this->to   = $to;
        $this->status = $status;
    }

    public function query()
    {
        $query = Osinergmin::query()->select([
            'id', 'uuid', 'plate', 'event', 'speed', 'latitude', 'longitude',
            'gpsDate', 'odometer', 'response_timestamp', 'response_message',
            'response_suggestion', 'response_status', 'created_at', 'updated_at',
        ]);

        if ($this->unit) {
            $query->where('uuid', $this->unit);
        }

        if ($this->status) {
            $query->where('response_status', $this->status);
        }

        // Filtrar por rango de fechas
        $query->whereBetween('created_at', [$this->from, $this->to])
              ->orderBy('id', 'DESC');

        return $query;
    }

    public function headings(): array
    {
        return [
            'ID','UUID','Placa','Evento','Velocidad','Latitud','Longitud',
            'Fecha GPS','Odómetro','Timestamp','Mensaje','Sugerencia','Estado','Creado','Actualizado'
        ];
    }
}
