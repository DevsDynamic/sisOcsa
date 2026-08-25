<?php

namespace App\Http\Controllers;

use App\Models\Osinergmin;
use App\Models\Person;
use Carbon\Carbon;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Auth;
//use DataTables;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Services\SystemConfig;

class OsinergminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        return view('osinergmins.index');
    }

    public function indexTable()
    {
        // Obtener clientes con token registrado y activo
        $clients_ocsa = Person::operationalClients()->whereNotNull('token')
            ->where('token', '<>', '')
            ->where('status', '1')
            ->get();

        $client = new Client(); // Instancia de Guzzle
        $response_result = []; // Array para respuestas de API
        $grouped_data = []; // Array donde agruparemos los datos por cliente

        foreach ($clients_ocsa as $client_ocsa) {
            $client_api = $client_ocsa->token;
            $client_name = $client_ocsa->full_name;
            $client_email = $client_ocsa->email ?? 'Sin correo';

            // API de Unidades
            $url_units = SystemConfig::ocsaBaseUrl() . config('services.ocsa.paths.units');
            // API de Empresas
            $url_companies = SystemConfig::ocsaBaseUrl() . config('services.ocsa.paths.companies');

            try {
                // Consultar datos de empresas
                $response_companies = $client->get($url_companies, [
                    'query' => [
                        'key' => $client_api // Si la API lo requiere
                    ] 
                ]);
                $companies_data = json_decode($response_companies->getBody(), true);

                // Crear un mapa de empresas para acceso rápido
                $companies_info = [];
                if (isset($companies_data['data']['companies']) && count($companies_data['data']['companies']) > 0) {
                    $company = $companies_data['data']['companies'][0]; // Un solo empresa por cliente
                    $companies_info = [
                        'company_id' => $company['id'],
                        'company_name' => $company['name'] ?? 'Desconocido',
                        'address' => $company['address'] ?? ''
                    ];
                }

                // Consultar datos de unidades
                $response_units = $client->get($url_units, [
                    'query' => [
                        'key' => $client_api,
                        'include' => ['ignition', 'battery_voltage', 'supply_voltage'],
                    ]
                ]);
                $response_data = json_decode($response_units->getBody(), true);

                // Si la API devuelve un error
                if (!is_array($response_data) || isset($response_data['error'])) {
                    $providerMessage = $response_data['error']['msg'] ?? 'Error no especificado por OCSA';
                    $error_message = ($providerMessage === "API key does not have any associated units")
                        ? "El cliente '$client_name' no tiene unidades registradas."
                        : $providerMessage;

                    // Registrar el error en el array de respuesta
                    $response_result[] = [
                        'status' => 'ERROR',
                        'unit' => [],
                        'response' => $response_data,
                        'error_message' => $error_message
                    ];
                    continue; // Saltar a la siguiente iteración
                }

                // Procesar datos de unidades
                if (!isset($response_data['data']['units'])) {
                    $response_result[] = [
                        'status' => 'ERROR',
                        'unit' => null,
                        'response' => [],
                        'error_message' => "No se encontraron unidades para el cliente '$client_name'."
                    ];
                    continue;
                }

                // Inicializar el cliente en grouped_data si no existe
                if (!isset($grouped_data[$client_ocsa->id])) {
                    $grouped_data[$client_ocsa->id] = [
                        'client_id' => $client_ocsa->id,
                        'nombre_cliente' => $client_name,
                        'correo' => $client_email,
                        'empresa' => $companies_info, // Asociamos la empresa directamente
                        'units' => [] // Inicializamos un array vacío para las unidades
                    ];
                }

                // Recorrer datos devueltos por la API de unidades
                foreach ($response_data['data']['units'] as $unit) {
                    if (!is_array($unit)) {
                        $unit = (array) $unit; // Convertir a array en caso necesario
                    }

                    // Agregar unidad al cliente
                    $grouped_data[$client_ocsa->id]['units'][] = [
                        'uuid' => $unit['unit_id'] ?? null,
                        'plate' => $unit['number'] ?? 'Desconocido', // Placa
                        'name_unit' => $unit['label'] ?? '', // Nombre de la unidad
                        'icon' => $unit['icon'] ?? '', // Tipo de unidad
                        'mileage' => $unit['mileage'] ?? 0, // Kilometraje
                        'last_update' => $unit['last_update'] ?? '', // Última actualización
                    ];
                }

            } catch (\Exception $e) {
                // Captura de errores en la petición
                $response_result[] = [
                    'status' => 'ERROR',
                    'unit' => null,
                    'response' => [],
                    'error_message' => "Error en la API para '$client_name': " . $e->getMessage()
                ];
            }
        }

        // Devolver los datos agrupados por cliente, donde cada cliente tiene una sola empresa y múltiples unidades
        return Datatables::of(collect($grouped_data)->values())
                        ->addIndexColumn()
                        ->addColumn('action', function ($data_unit) {
                            $buttons = '';

                            $clientId = $data_unit['client_id'] ?? null;

                            // Ver cliente
                            if (auth()->user()->can('people.show')) {
                                $buttons .= '<a href="" data-target="#modal-show" data-toggle="modal" data-id="' . $clientId . '">
                                                <button class="btn btn-info btn-sm mr-1 mb-1" title="Ver cliente">
                                                    <i class="fas fa-eye"></i> Ver
                                                </button>
                                            </a>';
                            }
                            // editar cliente
                            if (auth()->user()->can('people.edit')) {
                                $buttons .= '<a href="" data-target="#modal-edit" data-toggle="modal" data-id="' . $clientId . '">
                                                <button class="btn btn-warning btn-sm mr-1 mb-1" title="Editar cliente">
                                                    <i class="fas fa-edit"></i> Editar
                                                </button>
                                            </a>';
                            }
                            // Cambiar estado del cliente (activar/inactivar)
                            if (auth()->user()->can('people.change_status')) {                                    
                                $buttons .= '<a href="" data-target="#modal-change-status" data-toggle="modal" data-id="' . $clientId . '" data-status="inactivar">
                                                <button class="btn btn-sm mr-1 mb-1 btn-danger" title="Inactivar cliente">
                                                    <i class="fas fa-times-circle"></i> Inactivar
                                                </button>
                                            </a>';
                            }
                            
                    
                            // Mostrar botones o mensaje de sin permisos
                            if (!empty($buttons)) {
                                return $buttons;
                            } else {
                                return '<span class="badge badge-secondary">SIN PERMISOS</span>';
                            }
                        })
                        ->rawColumns(['action'])
                        ->make(true); 
    }

    public function indexUnits()
    {
        return view('osinergmins.index_units');
    }

    public function indexTableUnits()
    {
        try {
            // Obtener el usuario autenticado
            $user = Auth::user();
    
            // Buscar al cliente en Person por su user_id y validar si tiene un token activo
            $person = Person::where('user_id', $user->id)
                ->whereNotNull('token')
                ->where('token', '<>', '')
                ->where('status', '1')
                ->first(); // Obtener solo un resultado
    
            // Verificar si el cliente tiene un token
            if (!$person) {
                return response()->json(['message' => 'Cliente sin token registrado o inactivo'], 400);
            }
    
            // Token del cliente
            $client_api = $person->token;
    
            // Instancia de Guzzle
            $client = new Client();
    
            // API de Unidades
            $url_units = SystemConfig::ocsaBaseUrl() . config('services.ocsa.paths.units');
    
            // Consultar datos de unidades
            $response_units = $client->get($url_units, [
                'query' => ['key' => $client_api],
            ]);
            $response_data = json_decode($response_units->getBody(), true);

            // Validar respuesta de la API
            if (!isset($response_data['data']['units']) || empty($response_data['data']['units'])) {
                return response()->json([
                    'status' => 'ERROR',
                    'message' => "No se encontraron unidades para el cliente '{$user->username}'."
                ], 404);
            }

            // Formatear los datos en un array limpio
            $units_list = [];
            foreach ($response_data['data']['units'] as $unit) {
                $units_list[] = [
                    'uuid' => $unit['unit_id'] ?? null,
                    'plate' => $unit['number'] ?? 'Desconocido', // Placa
                    'name_unit' => $unit['label'] ?? '', // Nombre de la unidad
                    'icon' => $unit['icon'] ?? '', // Tipo de unidad
                    'mileage' => $unit['mileage'] ?? 0, // Kilometraje
                    'last_update' => $unit['last_update'] ?? '', // Última actualización
                ];
            }

            // Retornar los datos en formato JSON
            // return response()->json([
            //     'status' => 'SUCCESS',
            //     'units' => $units_list
            // ], 200);

        } catch (\Exception $e) {
            // Captura de errores en la petición
            return response()->json([
                'status' => 'ERROR',
                'message' => "Error al obtener unidades: " . $e->getMessage()
            ], 500);
        }

        // Enviar los datos formateados a DataTables
        return DataTables::of(collect($units_list))
                        ->addIndexColumn()
                        ->addColumn('action', function ($data_unit) {
                            return '<button class="btn btn-sm btn-info show-unit mt-2" 
                                            data-id="' . $data_unit['uuid'] . '"
                                            data-plate="' . $data_unit['plate'] . '">
                                            <i class="fas fa-eye"></i> Ver
                                        </button>';
                        })
                        ->rawColumns(['action'])
                        ->make(true);
    }

    public function indexTableUnitsV2()
    {
        $units = [];
        $errors = [];

        try {
            $user = Auth::user();
            $sources = $user->is_system_owner
                ? Person::activeGpsSources()->get()
                : Person::query()->where('user_id', $user->id)->activeGpsSources()->get();

            if ($sources->isEmpty()) {
                return DataTables::of(collect())->with('notice', 'No hay un cliente activo con token OCSA configurado.')->make(true);
            }

            $client = new Client(['timeout' => 20, 'connect_timeout' => 10, 'http_errors' => false]);
            $url = SystemConfig::ocsaBaseUrl() . config('services.ocsa.paths.units');

            foreach ($sources as $source) {
                $response = $client->get($url, ['query' => ['key' => $source->token]]);
                $body = json_decode((string) $response->getBody(), true);

                if ($response->getStatusCode() !== 200 || isset($body['error'])) {
                    $errors[] = $source->full_name . ': ' . ($body['error']['msg'] ?? 'HTTP '.$response->getStatusCode());
                    continue;
                }

                foreach ($body['data']['units'] ?? [] as $unit) {
                    $units[] = [
                        'uuid' => $unit['unit_id'] ?? null,
                        'plate' => $unit['number'] ?? 'Desconocido',
                        'name_unit' => $unit['label'] ?? '',
                        'mileage' => $unit['mileage'] ?? 0,
                        'last_update' => $unit['last_update'] ?? '',
                        'client_name' => $source->full_name,
                    ];
                }
            }
        } catch (\Throwable $exception) {
            $errors[] = 'Error al obtener unidades: '.$exception->getMessage();
        }

        return DataTables::of(collect($units))
            ->addIndexColumn()
            ->addColumn('action', fn ($unit) => '<button class="btn btn-sm btn-info show-unit" data-id="'.e($unit['uuid']).'" data-plate="'.e($unit['plate']).'"><i class="fas fa-eye"></i> Ver</button>')
            ->rawColumns(['action'])
            ->with('notice', implode(' | ', $errors))
            ->make(true);
    }

    public function create()
    {
        //
    }
    
    public function store(Request $request)
    {
        //
    }
    
    public function show(string $id)
    {
        //
    }
    
    public function retransmissionUnits(Request $request, $id)
    {
        abort_if(!is_string($id) || strlen($id) > 255, 404);

        $requestedLength = (int) $request->input('length', 25);
        $request->merge([
            'length' => $requestedLength < 1 ? 25 : min($requestedLength, 100),
        ]);

        // Obtener la fecha actual y restar un mes
        $fechaInicio = Carbon::now()->subDays(30)->startOfDay();
        $fechaFin = Carbon::now()->endOfDay();

        // Buscar la unidad por su uuid y filtrar por el último mes
        $query = Osinergmin::where('uuid', '=', $id)
            ->whereBetween('created_at', [$fechaInicio, $fechaFin])
            ->orderBy('id', 'DESC');

        // Verificar si existe la unidad
        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->addColumn('code', fn (Osinergmin $row) => 'OSIN' . str_pad((string) $row->id, 5, '0', STR_PAD_LEFT))
            ->toJson();
    }


    public function edit(string $id)
    {
        //
    }
    
    public function update(Request $request, string $id)
    {
        //
    }

    public function destroy(string $id)
    {
        //
    }
}
