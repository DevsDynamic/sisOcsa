<?php

namespace App\Http\Controllers;

use App\Models\Osinergmin;
use App\Models\Person;
use Carbon\Carbon;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
//use DataTables;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ReportController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('reports.index');
    }

    public function getRetransmissionsReport(Request $request)
    {
        // Validar fechas
        $request->validate([
            'from' => 'required|date',
            'to' => 'required|date|after_or_equal:from',
        ]);

        // Convertir las fechas a formato Carbon
        $from = Carbon::parse($request->from)->startOfDay();
        $to = Carbon::parse($request->to)->endOfDay();

        // Obtener datos de retransmisiones en el rango de fechas
        $retransmissions = Osinergmin::whereBetween('created_at', [$from, $to])->get();

        // Generar la vista parcial con los datos
        $html = view('partials.retransmissions_report', compact('retransmissions'))->render();

        return response()->json(['html' => $html]);
    }

    public function reportOsinergmin()
    {
        // Obtener clientes con token registrado y activo
        $clients_ocsa = Person::whereNotNull('token')
        ->where('token', '<>', '')
        ->where('status', '1')
        ->get();

        $client = new Client(); // Instancia de Guzzle
        $grouped_data = []; // Agrupación de unidades por cliente
        $unitOptions = []; // Arreglo con los datos de uuid y plate

        foreach ($clients_ocsa as $client_ocsa) {
        $client_api = $client_ocsa->token;
        $client_name = $client_ocsa->full_name;

        // API de Unidades
        $url_units = "https://monitoreo.ocsaperu.com/api/v1/unit/list.json?key=$client_api";

            try {
                // Consultar datos de unidades
                $response_units = $client->get($url_units, [
                    'query' => ['key' => $client_api]
                ]);
                $response_data = json_decode($response_units->getBody(), true);

                if (!isset($response_data['data']['units'])) {
                    continue;
                }

                // Recorrer unidades y agregar al array de opciones
                foreach ($response_data['data']['units'] as $unit) {
                    $unitOptions[] = [
                        'id' => $unit['unit_id'] ?? null,  // UUID de la unidad
                        'plate' => $unit['number'] ?? 'Desconocido' // Placa
                    ];
                }
            } catch (\Exception $e) {
                // En caso de error en la API, continuar con el siguiente cliente
                continue;
            }
        }

        return view('reports.osinergmin', compact('unitOptions'));
    }

    // public function viewReportOsinergmin(Request $request)
    // {
    //     //dd($request->unit);  // Verificar el valor de unit
    //     // Obtener la fecha actual y restar un mes
    //     $fechaInicio = Carbon::now()->subMonth()->startOfMonth();
    //     $fechaFin = Carbon::now()->endOfMonth();

    //     // Buscar la unidad por su uuid y filtrar por el último mes
    //     $unit_osinergmin = Osinergmin::where('uuid', '=', $request->unit)
    //         ->whereBetween('response_timestamp', [$fechaInicio, $fechaFin])
    //         ->orderBy('id', 'DESC')
    //         ->get();

    //     return Datatables::of($unit_osinergmin)
    //                 ->addIndexColumn()
    //                 ->make(true);
    // }

    public function viewReportOsinergmin(Request $request)
    {
        // Obtener la fecha actual y restar un mes
        $fechaInicio = Carbon::now()->subMonth()->startOfMonth();
        $fechaFin = Carbon::now()->endOfMonth();

        // Comenzar la consulta
        $unit_osinergmin = Osinergmin::query();

        // Si el parámetro unit está presente, filtrar por uuid
        if ($request->unit) {
            $unit_osinergmin = $unit_osinergmin->where('uuid', '=', $request->unit);
        }

        // Filtrar por el rango de fechas
        $unit_osinergmin = $unit_osinergmin
                            ->whereBetween('response_timestamp', [$fechaInicio, $fechaFin])
                            ->orderBy('id', 'DESC')
                            ->get();

        return Datatables::of($unit_osinergmin)
                    ->addIndexColumn()
                    ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
