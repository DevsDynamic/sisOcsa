<?php

namespace App\Http\Controllers;

use App\Models\Osinergmin;
use App\Models\Person;
use App\Services\TwilioService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use PhpParser\Node\Stmt\Return_;
use GuzzleHttp\Exception\RequestException;

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
        $fecha = Carbon::now()->addHours(5)->format('Y-m-d\TH:i:s.ssz'); // FECHA ACTUAL
        $parte1 = substr($fecha, 0, 20); // CONVERSION 1
        $parte2 = substr($fecha, 21, 3); // CONVERSION 2

        $Date = $parte1 . $parte2 . 'Z'; // ARMADO DE FECHA EN FORMATO PARA OSINERGMIN

        $token_trama = 'AD57D9F3-9DE3-41C6-BA49-F95856B3138F'; // TOKEN OSINERGMIN
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
                    //return $response->getBody();
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
                            $date_osinergmin = $resultado['timestamp']; // Guardar la fecha recibida
                            if (is_array($resultado['data']) && isset($resultado['data'][$key])) {
                                $response_data = $resultado['data'][$key]; // Toma el índice correspondiente
                            } else {
                                // Si no es un batch o no hay índice, usamos la respuesta completa de la clave 'data'
                                $response_data = $resultado['data'];
                            }
                        } else {
                            $date_osinergmin = $resultado['timestamp'];
                            // Si no existe la clave 'data', se considera una unidad
                            $response_data = $resultado;
                        }

                        // // Verificar si se recibió correctamente el estado
                        // $status = $estado == 200 ? 'SUCCESS' : 'ERROR';

                        // // Establecer el mensaje de respuesta
                        // $response_message = $estado == 200 
                        //     ? 'La trama se ha creado con éxito.' // En caso de éxito
                        //     : ($response_data['message'] ?? 'Sin mensaje de error'); // En caso de error

                        // // Establecer el mensaje de error
                        // $error_message = $estado == 200 
                        //     ? '' // No hay mensaje de error si el estado es 200
                        //     : ($response_data['message'] ?? 'Sin mensaje registrado del error'); // Mensaje de error si el estado no es 200
                            
                        // Verificar si $response_data tiene 'status'
                        $status = isset($response_data['status']) ? 
                        ($response_data['status'] === 'CREATED' ? 'SUCCESS' : 'ERROR') 
                        : 'ERROR';

                        // Asignar el mensaje basado en la respuesta de OSINERGMIN
                        $response_message = $response_data['message'] ?? 'Sin mensaje de respuesta';

                        // Establecer el mensaje de error solo si el estado es 'ERROR'
                        $error_message = ($status === 'ERROR') ? ($response_data['message'] ?? 'Sin mensaje registrado del error') : '';

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
                                'gpsDate' => now(),
                                'odometer' => $unit['odometer'],
                                'response_timestamp' => $date_osinergmin,
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

    public function checkAndSendAlerts()
    {
        //$twilio = new TwilioService();
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
                        'limit' => 1,
                        'include' => ['id', 'location', 'address', 'driver', 'name', 'surname']
                    ]
                ]);

                $alerts = json_decode($response->getBody(), true);

                if (!empty($alerts['data'])) {
                    $clientAlerts = 0;

                    foreach ($alerts['data'] as $alert) {
                        $message = "🚨 *Alerta Detectada* 🚨\n  ";
                        $message .= "📍 *Ubicación:* {$alert['address']}\n  ";
                        $message .= "📅 *Fecha/Hora:* {$alert['time']}\n  ";
                        $message .= "⚠ *Tipo:* {$alert['alert_type']}\n  ";
                        $message .= "📌 *Mensaje:* {$alert['msg']}\n  ";
                        
                        // construir link
                        $location = $alert['location'];
                        $mapsLink = "https://www.google.com/maps?q={$location}";
    
                        $message .= "🌍 *Ver en Maps:* {$mapsLink}\n";

                        // Asegurar que el mensaje no supere 4096 caracteres
                        $message = substr($message, 0, 4096);
                        $message = preg_replace('/[\t\n\r]+/', ' ', $message); // Elimina tabs y saltos de línea
                        $message = preg_replace('/\s{4,}/', ' ', $message);   // Reemplaza más de 4 espacios por uno solo 
                        
                        // Enviar el mensaje por cada alerta
                        $this->sendWhatsAppMessage($whatsappPhoneNumber, $message);
    
                        $clientAlerts++;
                        $totalAlerts++;
                    }

                    $alertsSummary[] = [
                        'cliente' => $client_ocsa->full_name,
                        'telefono' => $whatsappPhoneNumber,
                        'alertas' => $clientAlerts,
                    ];
                } else {
                    Log::info("No se encontraron alertas para {$client_ocsa->full_name}.");
                }
            } catch (\Exception $e) {
                Log::error("Error al consultar la API de OCSA para {$client_ocsa->full_name}: " . $e->getMessage());
            }
        }

        // Retornar el resumen de alertas
        return [
            'total_alertas' => $totalAlerts,
            'detalle_alertas' => $alertsSummary ?: 'No se encontraron alertas.',
        ];
    }

    // META PLANTILLA BASICA
    // public function sendWhatsAppMessage($phoneNumber, $message)
    // {
    //     $client = new Client();
    //     $apiUrl = 'https://graph.facebook.com/v22.0/535460832993968/messages';
    //     $apiToken = 'EAAOi1JftaOkBOwvFQkIvlKwNtnaBqGhcNBpL2wOd7MH7hGNlRr3PjMK3NbEaoZC5VAbes0gHoRrxk5RuvFSMt8lrbqMq5Swc4KbH2UAE5SZBNEtGLp9qMu8i8UEz602PzrDgdZCJVsX8LWW1g3ZA3AyKtHHLkqNsRyHGHmupcwZBKHrKt4pWjPj2qSwqLOKHNwFh6BhKGix2LeLsp7MZC3oSNSUWxW'; // Reemplaza con tu token de acceso de Meta
        
    //     try {
    //         // Mensaje usando plantilla (en tu caso 'hello_world')
    //         $response = $client->post($apiUrl, [
    //             'json' => [
    //                 'messaging_product' => 'whatsapp',
    //                 'to' => $phoneNumber,
    //                 'type' => 'template',
    //                 'template' => [
    //                     'name' => 'hello_world', // Nombre de la plantilla
    //                     'language' => [
    //                         'code' => 'en_US', // Código de idioma
    //                     ]
    //                 ]
    //             ],
    //             'headers' => [
    //                 'Authorization' => "Bearer $apiToken",
    //                 'Content-Type' => 'application/json'
    //             ]
    //         ]);

    //         Log::info('Mensaje de WhatsApp enviado con éxito.', ['response' => $response->getBody()]);
    //     } catch (\Exception $e) {
    //         Log::error('Error al enviar mensaje de WhatsApp: ' . $e->getMessage());
    //     }
    // }

    // META CON PLANTILLA PERSONALIZADA
    // public function sendWhatsAppMessage($phoneNumber, $message)
    // {
    //     $client = new Client();
    //     $apiUrl = 'https://graph.facebook.com/v22.0/535460832993968/messages';
    //     $apiToken = 'EAAOi1JftaOkBOwvFQkIvlKwNtnaBqGhcNBpL2wOd7MH7hGNlRr3PjMK3NbEaoZC5VAbes0gHoRrxk5RuvFSMt8lrbqMq5Swc4KbH2UAE5SZBNEtGLp9qMu8i8UEz602PzrDgdZCJVsX8LWW1g3ZA3AyKtHHLkqNsRyHGHmupcwZBKHrKt4pWjPj2qSwqLOKHNwFh6BhKGix2LeLsp7MZC3oSNSUWxW'; // Reemplaza con tu token de acceso de Meta
        
    //     try {
    //         // Mensaje usando plantilla (en tu caso 'hello_world')
    //         $response = $client->post($apiUrl, [
    //             'json' => [
    //                 'messaging_product' => 'whatsapp',
    //                 'to' => $phoneNumber,//'51921502571',
    //                 'type' => 'template',
    //                 'template' => [
    //                     'name' => 'alert_ocsa', // Nombre de la plantilla
    //                     'language' => [
    //                         'code' => 'es_PE', // Código de idioma
    //                     ],
    //                     'components' => [
    //                         [
    //                             'type' => 'header',
    //                             'parameters' => [
    //                                 [
    //                                     'type' => 'image',
    //                                     'image' => ['link' => 'https://ocsa.dmautomotriz.com/image/banner.jpg']
    //                                 ]
    //                             ]
    //                         ],
    //                         [
    //                             'type' => 'body',
    //                             'parameters' => [
    //                                 [
    //                                     'type' => 'text',
    //                                     'text' => $message//'Prueba sin msg dinámico',
    //                                 ]
    //                             ]
    //                         ]
    //                     ]
    //                 ]
    //             ],
    //             'headers' => [
    //                 'Authorization' => "Bearer $apiToken",
    //                 'Content-Type' => 'application/json'
    //             ]
    //         ]);

    //         Log::info('Mensaje de WhatsApp enviado con éxito.', ['response' => $response->getBody()]);
    //     } catch (\Exception $e) {
    //         Log::error('Error al enviar mensaje de WhatsApp: ' . $e->getMessage());
    //     }
    // }

    // PLANTILLA SIN IMAGEN DE CABECERA
    public function sendWhatsAppMessage($phoneNumber, $message)
    {
        $client = new Client();
        $apiUrl = 'https://graph.facebook.com/v22.0/535460832993968/messages';
        $apiToken = 'EAAOi1JftaOkBOwvFQkIvlKwNtnaBqGhcNBpL2wOd7MH7hGNlRr3PjMK3NbEaoZC5VAbes0gHoRrxk5RuvFSMt8lrbqMq5Swc4KbH2UAE5SZBNEtGLp9qMu8i8UEz602PzrDgdZCJVsX8LWW1g3ZA3AyKtHHLkqNsRyHGHmupcwZBKHrKt4pWjPj2qSwqLOKHNwFh6BhKGix2LeLsp7MZC3oSNSUWxW'; // Reemplaza con tu token de acceso de Meta
        
        try {
            // Mensaje usando plantilla (en tu caso 'hello_world')
            $response = $client->post($apiUrl, [
                'json' => [
                    'messaging_product' => 'whatsapp',
                    'to' => $phoneNumber,//'51921502571',
                    'type' => 'template',
                    'template' => [
                        'name' => 'alertas', // Nombre de la plantilla
                        'language' => [
                            'code' => 'es_PE', // Código de idioma
                        ],
                        'components' => [
                            [
                                'type' => 'header',
                                'parameters' => [
                                    [
                                        'type' => 'image',
                                        'image' => ['link' => 'https://ocsa.dmautomotriz.com/image/banner.jpg']
                                    ]
                                ]
                            ],
                            [
                                'type' => 'body',
                                'parameters' => [
                                    [
                                        'type' => 'text',
                                        'text' => $message//'Prueba sin msg dinámico',
                                    ]
                                ]
                            ]
                        ]
                    ]
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

    //VALIDAR QUE ESTÁ RETRANSMITIENDO
    public function checkUnitsStatus()
    {
        $clients = Person::whereNotNull('token')
                        ->where('token', '<>', '')
                        ->where('status', '1')
                        ->get();

        if ($clients->isEmpty()) {
            Log::info('No hay clientes activos con tokens.');
            return;
        }

        $clientHttp = new Client();
        $now = Carbon::now();
        $from = $now->subMinutes(20);

        // Variables para el resumen final
        $totalUnitsChecked = 0;
        $totalErrorsDetected = 0;
        $totalAlertsSent = 0;
        $summary = [];

        foreach ($clients as $client) {
            $client_api = $client->token;
            $url_units = "https://monitoreo.ocsaperu.com/api/v1/unit/list.json?key=$client_api";

            try {
                $response_units = $clientHttp->get($url_units);
                $response_data = json_decode($response_units->getBody(), true);

                if (!isset($response_data['data']['units']) || empty($response_data['data']['units'])) {
                    Log::info("No se encontraron unidades para el cliente {$client->full_name}.");
                    continue;
                }

                foreach ($response_data['data']['units'] as $unit) {
                    $unit_id = $unit['unit_id'] ?? null;
                    $plate = $unit['number'] ?? 'Desconocido';

                    // Buscar registros en la tabla osinergmins de los últimos 20 minutos
                    $errorRecords = Osinergmin::where('uuid', $unit_id)
                        ->where('created_at', '>=', $from)
                        ->where('response_status', 'ERROR')
                        ->count();

                    $successRecords = Osinergmin::where('uuid', $unit_id)
                        ->where('created_at', '>=', $from)
                        ->where('response_status', 'SUCCESS')
                        ->count();

                    $totalUnitsChecked++;

                    // Si hay errores y no hay éxitos, enviar alerta
                    if ($errorRecords > 0 && $successRecords === 0) {
                        $totalErrorsDetected++;

                        $message = "⚠ *Alerta de Unidad*  ";
                        $message .= "🚗 *Placa:* $plate  ";
                        $message .= "❌ *Estado:* Dejó de retransmitir a OSINERGMIN en los últimos 20 minutos  ";
                        //$message .= "📍 *Última ubicación:* https://www.google.com/maps?q={$unit['latitude']},{$unit['longitude']}\n";
                        $message .= "🕒 *Último intento:* " . Carbon::parse($unit['last_update'])->format('Y-m-d H:i:s');

                        $this->sendWhatsAppMessage("51" . $client->phone_number, $message);
                        Log::info("Mensaje de alerta enviado para la unidad $plate del cliente {$client->full_name}.");
                        $totalAlertsSent++;

                        // Agregar al resumen
                        $summary[] = [
                            'cliente' => $client->full_name,
                            'unidad' => $plate,
                            'errores_detectados' => $errorRecords,
                            'ultimo_registro' => Carbon::parse($unit['last_update'])->format('Y-m-d H:i:s')
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::error("Error al consultar la API de unidades para {$client->full_name}: " . $e->getMessage());
            }
        }

        // Resumen final
        $result = [
            'total_unidades_verificadas' => $totalUnitsChecked,
            'total_unidades_con_errores' => $totalErrorsDetected,
            'total_alertas_enviadas' => $totalAlertsSent,
            'detalles' => $summary
        ];

        Log::info("Resumen del proceso:", $result);
        return response()->json($result, 200);
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
