<?php

namespace App\Http\Controllers;

use App\Models\Osinergmin;
use App\Models\Person;
use Carbon\Carbon;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use PhpParser\Node\Stmt\Return_;

class TaskController extends Controller
{    
    public function sendDataOsinergmin()
    {
        // OBTENER CLIENTES DE OCSA CON TOKEN REGISTRADO;
        $clients_ocsa = Person::whereNotNull('token')
                        ->where('token', '<>', '')
                        ->where('status', '1')
                        ->get();

        $resu = []; // Inicializar arreglo para almacenar resultados
        $fecha = Carbon::now()->format('Y-m-d\TH:i:s.ssz'); // FECHA ACTUAL
        $parte1 = substr($fecha, 0, 20); // CONVERSION 1
        $parte2 = substr($fecha, 21, 3); // CONVERSION 2

        $Date = $parte1 . $parte2 . 'Z'; // ARMADO DE FECHA EN FORMATO PARA OSINERGMIN

        $token_trama = 'EE089GA4-888A-4216-AC42-53683E236F18'; // TOKEN OSINERGMIN
        // DEV: EE089GA4-888A-4216-AC42-53683E236F18
        // PROD: AD57D9F3-9DE3-41C6-BA49-F95856B3138F

        foreach ($clients_ocsa as $client_ocsa) {
            $client_api = $client_ocsa->token;
            $client_name = $client_ocsa->full_name;

            $url = 'https://monitoreo.ocsaperu.com/api/v1/unit/list.json'; // API OCSA OBTENER DATOS
            $apiKey = $client_api; // TOKEN
            
            $client = new Client(); // CREAR INSTANCIA DE GUZZLE CLIENT

            try { // SOLICITUD GET USANDO GUZZLE
                $response = $client->get($url, [ 
                    'query' => [
                        'key' => $apiKey,
                        'include' => ['ignition', 'battery_voltage', 'supply_voltage']
                    ]
                ]);
                
                //$customer_data = json_decode($response->getBody(), true); // RESPUESTA API OCSA COMO ARRAY
                $response_data = json_decode($response->getBody(), true); // RESPUESTA API COMO ARRAY

                // return $customer_data;
                $data_send = []; // INICIALIZAR $item COMO ARRAY VACIO
                
                // Si la respuesta contiene un error, lo manejamos y NO hacemos el recorrido
                if (isset($response_data['error'])) {
                    $error_code = $response_data['error']['code'];
                    $error_message = ($response_data['error']['msg'] === "API key does not have any associated units") 
                    ? "El cliente: " .$client_ocsa->full_name. " con token: " .$apiKey. ", no tiene unidades registradas." 
                    : $response_data['error']['msg'];

                    // Agregar el error al array de resultados para mostrarlo en la tabla
                    $resu[] = [
                        'status' => 'ERROR',
                        'unit' => [],
                        'response' => $response_data,  
                        'bbdd' => true,
                        'error_message' => $error_message
                    ];

                } else {
                    // NO hay error, procedemos con el recorrido de unidades
                    $customer_data = $response_data;

                    // PROCESAR DATOS DEVUELTO POR API OCSA
                    foreach ($customer_data as $nombre => $data) {
                        foreach ($data as $key => $unit) {
                            foreach ($unit as $unidad => $value) {
                                // Verificar si ignition existe y asignar su valor
                                $ignition = $value['ignition']['value'] ?? null;  // Si no existe, será null
                                $supply_voltage = $value['supply_voltage']['value'] ?? null; // Si supply_voltage no existe, será null

                                // Lógica de eventos basada en los valores de ignition y supply_voltage
                                if ($ignition === "on") {
                                    $evento = "acc_on"; // Si ignition está encendido
                                } else if ($ignition === "off") {
                                    // Si ignition está apagado, se revisa supply_voltage
                                    if ($supply_voltage > 0) {
                                        $evento = "acc_off"; // Si supply_voltage > 0
                                    } else {
                                        $evento = "battery_dc"; // Si supply_voltage es 0
                                    }
                                } else {
                                    $evento = "none"; // Si ignition no está presente o no tiene valor esperado
                                }

                                // Determinar el tipo (batch o unit) y configurar el arreglo de datos
                                $type = (count($unit) > 1) ? "batch" : "unit"; // Determinar el tipo

                                // Armar el array de datos común
                                $data_unit = [
                                    'event' => $evento,
                                    'plate' => $value['number'],
                                    'speed' => (double) $value['speed'],
                                    'position' => [
                                        'latitude' => $value['lat'],
                                        'longitude' => $value['lng'],
                                        'altitude' => "169"
                                    ],
                                    'gpsDate' => $Date,
                                    'tokenTrama' => $token_trama,
                                    'odometer' => intval($value['mileage'] / 1000),
                                    'uuid' => $value['unit_id'],
                                ];

                                // Agregar el resultado al array final
                                $data_send[] = array_merge($data_unit, ['type' => $type]);
                            }
                        }
                    }

                    // return $data_send;// JSON a enviar a OSINERGMIN

                    // Si es "unit", envía el objeto directamente (sin array).
                    if ($type === "unit") {
                        $data_send_api = $data_send[0]; // Extrae el único objeto del array
                    } else {
                        $data_send_api = $data_send;
                    }

                    //return $data_send;
                    // SELECCIONAR API OSINERGMIN POR BATH O UNIT
                    $urlEndpoint = ($type === "batch") ? 'https://prod.osinergmin-agent-2021.com/api/v1/trama-batch' : 'https://prod.osinergmin-agent-2021.com/api/v1/trama';

                    $mihttp = new Client(); // CREAR INSTANCIA DE HTTP

                    // SOLICITUD POST USANDO CURL
                    $response = $mihttp->request('POST', $urlEndpoint, [
                        'headers' => ['Content-Type' => 'application/json'],
                        'body' => json_encode($data_send_api) // CONVIERTE ARRAY A JSON
                    ]);
                    
                    // $estado = $response->getStatusCode(); // Obtener el estado de la respuesta                
                    // $resultado = $response->getBody()->getContents(); // Obtener el contenido de la respuesta
                    
                    $estado = $response->getStatusCode();
                    $resultado = json_decode($response->getBody()->getContents(), true);
                    //return $data_send;
                    // Asegurar que $data_send sea un array
                    if (!is_array($data_send)) {
                        $data_send = [$data_send]; // Convertir en un array si es un objeto o cadena
                    }
                    //return $data_send;
                    // Procesar la respuesta
                    foreach ($data_send as $key => $unit) {
                        //return $unit;
                        if (!is_array($unit)) {
                            // Log para identificar el problema
                            Log::error("Formato inesperado en \$unit", ['unit' => $unit]);
                            continue; // Saltar elementos con formato incorrecto
                        }

                        // Verificar si $resultado contiene la clave 'data'
                        if (isset($resultado['data'])) {
                            // Si es un batch (array con múltiples elementos)
                            if (is_array($resultado['data']) && isset($resultado['data'][$key])) {
                                $response_data = $resultado['data'][$key]; // Toma el índice correspondiente
                            } else {
                                // Si no es un batch o no hay índice, usamos la respuesta completa de la clave 'data'
                                $response_data = $resultado['data'];
                            }
                        } else {
                            // Si no existe la clave 'data', se considera una unidad
                            $response_data = $resultado;
                        }

                        // Verificar si se recibió correctamente el estado
                        $status = $estado == 200 ? 'SUCCESS' : 'ERROR';

                        // Establecer el mensaje de respuesta
                        $response_message = $estado == 200 
                            ? 'La trama se ha creado con éxito.' // En caso de éxito
                            : ($response_data['message'] ?? 'Sin mensaje de error'); // En caso de error

                        // Establecer el mensaje de error
                        $error_message = $estado == 200 
                            ? '' // No hay mensaje de error si el estado es 200
                            : ($response_data['message'] ?? 'Sin mensaje registrado del error'); // Mensaje de error si el estado no es 200
                            
                        // Obtener la sugerencia si está presente
                        $response_suggestion = $response_data['suggestion'] ?? null;

                        if (!isset($unit['uuid'], $unit['plate'], $unit['position']['latitude'], $unit['position']['longitude'])) {
                            Log::warning("Datos incompletos recibidos de OSINERGMIN", ['unit' => $unit]);
                            continue;
                        }

                        // Intentar almacenar en la base de datos
                        try {
                            Osinergmin::create([
                                'uuid' => $unit['uuid'],
                                'plate' => $unit['plate'],
                                'event' => $unit['event'],
                                'speed' => $unit['speed'],
                                'latitude' => $unit['position']['latitude'],
                                'longitude' => $unit['position']['longitude'],
                                'gpsDate' => $unit['gpsDate'],
                                'odometer' => $unit['odometer'],
                                'response_timestamp' => now(),
                                'response_message' => $response_message,
                                'response_suggestion' => $response_suggestion,
                                'response_status' => $status ?? null
                            ]);
                            $db_status = true;
                        } catch (\Exception $e) {
                            Log::error("Error al guardar en la base de datos: " . $e->getMessage());
                            $db_status = false;
                        }

                        // Agregar resultado al arreglo final
                        $resu[] = [
                            'status' => $status,
                            'unit' => $unit,
                            'response' => $response_data,  // Esta línea maneja tanto batch como unidad
                            'bbdd' => $db_status,
                            'error_message' => $error_message
                        ];
                    }
                }
                
            } catch (\Exception $e) {
                $resu[] = ['status' => 'ERROR', 'error_message' => $e->getMessage()];
            }
        }
        return view('welcome', compact('resu', 'Date'));
        //return $resu;
    }

    public function sendAlertWhatsApp()
    {

    }

    // public function checkAndSendAlerts()
    // {
    //     $clients_ocsa = Person::whereNotNull('token')
    //                         ->where('token', '<>', '')
    //                         ->where('status', '1')
    //                         ->get();

    //     foreach ($clients_ocsa as $client_ocsa) {
    //         $whatsappPhoneNumber = '51' . $client_ocsa->phone_number;

    //         $now = Carbon::now('UTC');
    //         $from = $now->subMinutes(2)->format('Y-m-d\TH:i:s\Z');
    //         $till = $now->format('Y-m-d\TH:i:s\Z');

    //         $url = "https://monitoreo.ocsaperu.com/api/v1/alert/list.json";

    //         $client = new Client();

    //         try {
    //             $response = $client->get($url, [
    //                 'query' => [
    //                     'key' => $client_ocsa->token,
    //                     'from' => $from,
    //                     'till' => $till,
    //                     'limit' => 150,
    //                     'include' => ['id', 'location', 'address', 'driver', 'name', 'surname']
    //                 ]
    //             ]);

    //             $alerts = json_decode($response->getBody(), true);

    //             if (!empty($alerts['data'])) {
    //                 foreach ($alerts['data'] as $alert) {
    //                     $message = "🚨 *Alerta detectada* 🚨\n";
    //                     $message .= "📍 *Ubicación:* {$alert['address']}\n";
    //                     $message .= "📅 *Fecha/Hora:* {$alert['time']}\n";
    //                     $message .= "⚠ *Tipo:* {$alert['alert_type']}\n";
    //                     $message .= "📌 *Mensaje:* {$alert['msg']}";

    //                     $this->sendWhatsAppMessage($whatsappPhoneNumber, $message);
    //                 }
    //             } else {
    //                 Log::info('No se encontraron alertas en los últimos 2 minutos.');
    //             }
    //         } catch (\Exception $e) {
    //             Log::error('Error al consultar la API de OCSA: ' . $e->getMessage());
    //         }
    //     }
    // }

    public function checkAndSendAlerts()
    {
        $clients_ocsa = Person::whereNotNull('token')
                            ->where('token', '<>', '')
                            ->where('status', '1')
                            ->get();

        $totalAlerts = 0;
        $alertsSummary = [];

        foreach ($clients_ocsa as $client_ocsa) {
            $whatsappPhoneNumber = '51' . $client_ocsa->phone_number;

            $now = Carbon::now('UTC');
            //$from = $now->subMinutes(2)->format('Y-m-d\TH:i:s\Z');
            $from = '2025-03-24T00:00:00Z';
            $till = $now->format('Y-m-d\TH:i:s\Z');

            $url = "https://monitoreo.ocsaperu.com/api/v1/alert/list.json";

            $client = new Client();

            try {
                $response = $client->get($url, [
                    'query' => [
                        'key' => $client_ocsa->token,
                        'from' => $from,
                        'till' => $till,
                        'limit' => 2,//150
                        'include' => ['id', 'location', 'address', 'driver', 'name', 'surname']
                    ]
                ]);

                $alerts = json_decode($response->getBody(), true);

                if (!empty($alerts['data'])) {
                    $message = "🚨 *Resumen de Alertas* 🚨\n\n";
                    $clientAlerts = 0;

                    foreach ($alerts['data'] as $alert) {
                        $message .= "📍 *Ubicación:* {$alert['address']}\n";
                        $message .= "📅 *Fecha/Hora:* {$alert['time']}\n";
                        $message .= "⚠ *Tipo:* {$alert['alert_type']}\n";
                        $message .= "📌 *Mensaje:* {$alert['msg']}\n";
                        $message .= "----------------------------\n";

                        $clientAlerts++;
                        $totalAlerts++;
                    }

                    $alertsSummary[] = [
                        'cliente' => $client_ocsa->full_name,
                        'telefono' => $whatsappPhoneNumber,
                        'alertas' => $clientAlerts,
                    ];

                    $message = substr($message, 0, 4096);

                    $this->sendWhatsAppMessage($whatsappPhoneNumber, $message);
                } else {
                    Log::info("No se encontraron alertas en los últimos 2 minutos para {$client_ocsa->name}.");
                }
            } catch (\Exception $e) {
                Log::error("Error al consultar la API de OCSA para {$client_ocsa->name}: " . $e->getMessage());
            }
        }

        // Retornar el resumen de alertas
        return [
            'total_alertas' => $totalAlerts,
            'detalle_alertas' => $alertsSummary ?: 'No se encontraron alertas en los últimos 2 minutos.',
        ];
    }

    public function sendWhatsAppMessage($phoneNumber, $message)
    {
        $client = new Client();
        $apiUrl = 'https://graph.facebook.com/v18.0/535460832993968/messages';
        $apiToken = 'EAAOi1JftaOkBOwYm7KH10rC4bbJxyXTBloZApSCFQjDMwLqswnF08Fx0q2j1tT9ZAdt7K2lOFkemQlyLuSTdbk6ZC6RTeZAUTNkZB8ZCDH6Im0gAyjR6SIVZBUZCZAcZAqTpZAo44A8nC7KHFdDYv75s3i6PbJm3YVZB8ZAz1zGJxPMq3JDjvTkZBcc3oZBJlSHO1EBcZBEeAR1zPZCQMwBKs7RXdf420vd5cGXQZD'; // Reemplaza con tu token de acceso de Meta

        try {
            $response = $client->post($apiUrl, [
                'json' => [
                    'messaging_product' => 'whatsapp',
                    'to' => $phoneNumber,
                    'type' => 'text',
                    'text' => ['body' => $message]
                ],
                'headers' => [
                    'Authorization' => "Bearer $apiToken",
                    'Content-Type' => 'application/json'
                ]
            ]);

            Log::info('Mensaje de WhatsApp enviado con éxito.', ['response' => $response->getBody()]);
        } catch (\Exception $e) {
            Log::error('Error al enviar mensaje de WhatsApp: ' . $e->getMessage());
        }
    }

    public function index()
    {
        //
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
