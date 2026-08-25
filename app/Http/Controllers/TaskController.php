<?php

namespace App\Http\Controllers;

use App\Models\Osinergmin;
use App\Models\Person;
use App\Services\TwilioService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use PhpParser\Node\Stmt\Return_;
use GuzzleHttp\Exception\RequestException;
use App\Services\SystemConfig;
use App\Models\IntegrationLog;

class TaskController extends Controller
{
    public function sendDataOsinergmin(?string $environment = null)
    {
        $lock = Cache::lock('osinergmin:send', 600);

        if (!$lock->get()) {
            Log::warning('Se omitio el envio a Osinergmin porque ya existe una ejecucion activa.');
            $this->integrationLog($environment ?? SystemConfig::environment(), 'RUN', 'SKIPPED', 'Ejecución omitida porque otro envío conserva el bloqueo.');

            return response()->json([
                'status' => 'SKIPPED',
                'message' => 'Ya existe un envio a Osinergmin en proceso.',
            ], 409);
        }

        try {
            return $this->processDataOsinergmin($environment);
        } finally {
            $lock->release();
        }
    }

    private function processDataOsinergmin(?string $environment = null)
    {
        // OBTENER CLIENTES DE OCSA CON TOKEN REGISTRADO;
        $clients_ocsa = Person::activeGpsSources()->get();

        $resu = []; // Inicializar arreglo para almacenar resultados
        // Se usa la hora de consulta porque la fecha del proveedor GPS no es confiable.
        $Date = Carbon::now('UTC')->format('Y-m-d\TH:i:s.v\Z');

        $osinergmin_environment = $environment ?? SystemConfig::environment();

        if (!in_array($osinergmin_environment, ['production', 'development'], true)) {
            throw new \InvalidArgumentException('Ambiente de Osinergmin no valido.');
        }
        $this->integrationLog($osinergmin_environment, 'RUN', 'STARTED', 'Ejecución de retransmisión iniciada.');
        $token_trama = SystemConfig::osinergminToken($osinergmin_environment);

        if (empty($token_trama)) {
            $this->integrationLog($osinergmin_environment, 'CONFIG', 'ERROR', "No existe token Osinergmin para {$osinergmin_environment}.");
            throw new \RuntimeException(
                "No se ha configurado el token de Osinergmin para el ambiente {$osinergmin_environment}."
            );
        }

        if ($clients_ocsa->isEmpty()) {
            $message = 'No hay contactos activos con un token GPS configurado.';
            Log::warning($message);
            $this->integrationLog($osinergmin_environment, 'OCSA', 'ERROR', $message);
            $resu[] = [
                'status' => 'ERROR', 'unit' => [], 'response' => [],
                'bbdd' => false, 'error_message' => $message,
            ];
        }

        foreach ($clients_ocsa as $client_ocsa) {
            $client_api = $client_ocsa->token;
            $client_name = $client_ocsa->full_name;
            $resultStart = count($resu);

            $url = SystemConfig::ocsaBaseUrl() . config('services.ocsa.paths.units');
            $apiKey = $client_api; // TOKEN

            //$client = new Client(); // CREAR INSTANCIA DE GUZZLE CLIENT
            $client = new Client([
                'timeout' => 20,
                'connect_timeout' => 10,
                'http_errors' => false
            ]);

            try { // SOLICITUD GET USANDO GUZZLE
                $response = $client->get($url, [
                    'query' => [
                        'key' => $apiKey,
                        'include' => [
                            'ignition',
                            'battery_voltage',
                            'supply_voltage'
                        ]
                    ]
                ]);

                $status = $response->getStatusCode();

                // VALIDAR QUE OCSA RESPONDIÓ HTTP 200
                if ($status !== 200) {

                    $body = mb_substr((string) $response->getBody(), 0, 500);
                    $this->integrationLog($osinergmin_environment, 'OCSA', 'ERROR', "OCSA respondió HTTP {$status}: {$body}", $client_ocsa->id, $status);

                    $resu[] = [
                        'status' => 'ERROR',
                        'unit' => [],
                        'response' => [],
                        'bbdd' => false,
                        'error_message' => "Cliente {$client_name}: OCSA respondió HTTP {$status}. {$body}"
                    ];

                    continue;
                }

                $response_data = json_decode((string) $response->getBody(), true);

                // VALIDAR QUE EL JSON SEA VÁLIDO
                if (json_last_error() !== JSON_ERROR_NONE) {

                    $this->integrationLog($osinergmin_environment, 'OCSA', 'ERROR', 'OCSA devolvió una respuesta JSON inválida.', $client_ocsa->id, $status);

                    $resu[] = [
                        'status' => 'ERROR',
                        'unit' => [],
                        'response' => [],
                        'bbdd' => false,
                        'error_message' => "Cliente {$client_name}: Respuesta JSON inválida."
                    ];

                    continue;
                }

                // SI LA API DEVUELVE ERROR
                if (isset($response_data['error'])) {

                    $providerMessage = $response_data['error']['msg'] ?? 'Error no especificado por OCSA';
                    $error_message = ($providerMessage === "API key does not have any associated units")
                        ? "Cliente {$client_name}: no tiene unidades registradas."
                        : "Cliente {$client_name}: {$providerMessage}";
                    $this->integrationLog($osinergmin_environment, 'OCSA', 'ERROR', $error_message, $client_ocsa->id, $status);

                    $resu[] = [
                        'status' => 'ERROR',
                        'unit' => [],
                        'response' => $response_data,
                        'bbdd' => false,
                        'error_message' => $error_message
                    ];

                    continue;
                }

                $data_send = [];
                $type = 'batch'; // INICIALIZAR $item COMO ARRAY VACIO


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
                                'speed' => (float) $value['speed'],
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

                // Si es "unit", envía el objeto directamente (sin array).
                if (empty($data_send)) {
                    Log::warning('El proveedor GPS no devolvio unidades procesables.', [
                        'cliente' => $client_name,
                    ]);
                    $this->integrationLog($osinergmin_environment, 'OCSA', 'WARNING', "Cliente {$client_name}: no se recibieron unidades procesables.", $client_ocsa->id);
                    $resu[] = [
                        'status' => 'ERROR', 'unit' => [], 'response' => $response_data,
                        'bbdd' => false,
                        'error_message' => "Cliente {$client_name}: OCSA no devolvió unidades procesables.",
                    ];
                    continue;
                }

                $type = count($data_send) > 1 ? 'batch' : 'unit';
                foreach ($data_send as &$data_item) {
                    $data_item['type'] = $type;
                }
                unset($data_item);

                if ($type === "unit") {
                    $data_send_api = $data_send[0]; // Extrae el único objeto del array
                } else {
                    $data_send_api = $data_send;
                }

                //return $data_send;
                // SELECCIONAR API OSINERGMIN POR BATH O UNIT
                $urlEndpoint = SystemConfig::osinergminEndpoint($osinergmin_environment, $type);

                $mihttp = new Client([
                    'timeout' => 25,
                    'connect_timeout' => 10,
                    'http_errors' => false,
                ]); // CREAR INSTANCIA DE HTTP

                // SOLICITUD POST USANDO CURL
                $response = $mihttp->request('POST', $urlEndpoint, [
                    'headers' => ['Content-Type' => 'application/json'],
                    'body' => json_encode($data_send_api) // CONVIERTE ARRAY A JSON
                ]);

                $estado = $response->getStatusCode();
                $responseBody = $response->getBody()->getContents();
                $contentType = strtolower($response->getHeaderLine('Content-Type'));

                // El WAF de PMGO puede responder HTTP 200 con una página HTML
                // "Request Rejected". Eso no es una respuesta válida del API.
                if (str_contains($contentType, 'text/html') || preg_match('/<html|request rejected/i', $responseBody)) {
                    preg_match('/support\s+id\s+is\s*:\s*([0-9]+)/i', strip_tags($responseBody), $supportMatch);
                    $supportId = $supportMatch[1] ?? null;
                    $message = str_contains(strtolower($responseBody), 'request rejected')
                        ? 'El firewall de Osinergmin rechazó la solicitud antes de llegar al API.'
                        : 'PMGO respondió HTML en lugar del JSON esperado.';
                    if ($supportId) {
                        $message .= " Support ID: {$supportId}.";
                    }

                    $this->integrationLog(
                        $osinergmin_environment,
                        'WAF',
                        'ERROR',
                        "Cliente {$client_name}: {$message}",
                        $client_ocsa->id,
                        $estado,
                        [
                            'endpoint' => $urlEndpoint,
                            'content_type' => $contentType,
                            'support_id' => $supportId,
                            'response_excerpt' => mb_substr(strip_tags($responseBody), 0, 1000),
                        ]
                    );

                    $resu[] = [
                        'status' => 'ERROR',
                        'unit' => [],
                        'response' => ['support_id' => $supportId],
                        'bbdd' => false,
                        'error_message' => $message,
                    ];
                    continue;
                }

                $resultado = json_decode($responseBody, true);

                if (json_last_error() !== JSON_ERROR_NONE || ! is_array($resultado)) {
                    $message = 'PMGO devolvió una respuesta que no es JSON válido: '.json_last_error_msg().'.';
                    $this->integrationLog(
                        $osinergmin_environment,
                        'OSINERGMIN',
                        'ERROR',
                        "Cliente {$client_name}: {$message}",
                        $client_ocsa->id,
                        $estado,
                        ['endpoint' => $urlEndpoint, 'response_excerpt' => mb_substr($responseBody, 0, 1000)]
                    );
                    $resu[] = ['status' => 'ERROR', 'unit' => [], 'response' => [], 'bbdd' => false, 'error_message' => $message];
                    continue;
                }

                if ($estado < 200 || $estado >= 300) {
                    $this->integrationLog($osinergmin_environment, 'OSINERGMIN', 'ERROR', "Osinergmin respondió HTTP {$estado}: " . mb_substr($responseBody, 0, 500), $client_ocsa->id, $estado);
                    throw new \RuntimeException(
                        "Osinergmin respondio HTTP {$estado}: " . mb_substr($responseBody, 0, 500)
                    );
                }

                // Asegurar que $data_send sea un array
                if (!is_array($data_send)) {
                    $data_send = [$data_send]; // Convertir en un array si es un objeto o cadena
                }

                // Procesar la respuesta
                foreach ($data_send as $key => $unit) {
                    if (!is_array($unit)) {
                        // Log para identificar el problema
                        Log::error("Formato inesperado en \$unit", ['unit' => $unit]);
                        continue; // Saltar elementos con formato incorrecto
                    }

                    // Verificar si $resultado contiene la clave 'data'
                    if (isset($resultado['data'])) {
                        // Si es un batch (array con múltiples elementos)
                        $date_osinergmin = $resultado['timestamp'] ?? now()->toIso8601String();
                        if (is_array($resultado['data']) && isset($resultado['data'][$key])) {
                            $response_data = $resultado['data'][$key]; // Toma el índice correspondiente
                        } else {
                            // Si no es un batch o no hay índice, usamos la respuesta completa de la clave 'data'
                            $response_data = $resultado['data'];
                        }
                    } else {
                        $date_osinergmin = $resultado['timestamp'] ?? now()->toIso8601String();
                        // Si no existe la clave 'data', se considera una unidad
                        $response_data = $resultado;
                    }

                    // Verificar si $response_data tiene 'status'
                    $status = $this->providerStatus($response_data, $resultado, $estado);

                    // Asignar el mensaje basado en la respuesta de OSINERGMIN
                    $generalMessage = $this->providerMessage(
                        $resultado,
                        $estado === 202 ? 'Trama aceptada por PMGO para procesamiento.' : 'Osinergmin no devolvió un mensaje descriptivo.'
                    );
                    $response_message = $this->providerMessage($response_data, $generalMessage);

                    // Establecer el mensaje de error solo si el estado es 'ERROR'
                    $error_message = $status === 'ERROR' ? $response_message : '';

                    // Obtener la sugerencia si está presente
                    $response_suggestion = $response_data['suggestion']
                        ?? $response_data['recommendation']
                        ?? $resultado['suggestion']
                        ?? $resultado['recommendation']
                        ?? null;
                    if (is_array($response_suggestion)) {
                        $response_suggestion = json_encode($response_suggestion, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                    }

                    if ($status === 'ERROR') {
                        $plate = $unit['plate'] ?? 'sin placa';
                        $detail = "Placa {$plate}: {$response_message}";
                        if (filled($response_suggestion)) {
                            $detail .= " Sugerencia: {$response_suggestion}";
                        }
                        $this->integrationLog(
                            $osinergmin_environment,
                            'OSINERGMIN',
                            'ERROR',
                            $detail,
                            $client_ocsa->id,
                            $estado,
                            ['plate' => $plate, 'endpoint' => $urlEndpoint, 'item_response' => $response_data, 'full_response' => $resultado]
                        );
                    } elseif ($status === 'UNKNOWN') {
                        $plate = $unit['plate'] ?? 'sin placa';
                        $this->integrationLog(
                            $osinergmin_environment,
                            'OSINERGMIN',
                            'WARNING',
                            "Placa {$plate}: HTTP {$estado}, pero la respuesta no indicó si fue aceptada o rechazada. {$response_message}",
                            $client_ocsa->id,
                            $estado,
                            ['plate' => $plate, 'endpoint' => $urlEndpoint, 'item_response' => $response_data, 'full_response' => $resultado]
                        );
                    }

                    if (!isset($unit['uuid'], $unit['plate'], $unit['position']['latitude'], $unit['position']['longitude'])) {
                        Log::warning("Datos incompletos recibidos de OSINERGMIN", ['unit' => $unit]);
                        continue;
                    }

                    // Intentar almacenar en la base de datos
                    try {
                        Osinergmin::create([
                            'person_id' => $client_ocsa->id,
                            'environment' => $osinergmin_environment,
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
                $clientResults = collect(array_slice($resu, $resultStart));
                $clientErrors = $clientResults->where('status', 'ERROR')->count();
                $clientSuccesses = $clientResults->where('status', 'SUCCESS')->count();
                $clientUnknown = $clientResults->where('status', 'UNKNOWN')->count();
                $this->integrationLog(
                    $osinergmin_environment,
                    'OSINERGMIN',
                    $clientErrors > 0 ? 'ERROR' : ($clientUnknown > 0 ? 'WARNING' : 'SUCCESS'),
                    "Cliente {$client_name}: {$clientSuccesses} aceptadas, {$clientErrors} rechazadas y {$clientUnknown} sin estado concluyente.",
                    $client_ocsa->id,
                    $estado,
                    ['endpoint' => $urlEndpoint, 'type' => $type]
                );
            // } catch (\Exception $e) {
            //     $resu[] = ['status' => 'ERROR', 'error_message' => $e->getMessage()];
            } catch (\Throwable $e) {

                Log::error('Error procesando cliente OCSA', [
                    'cliente' => $client_name,
                    'mensaje' => $e->getMessage(),
                    'archivo' => $e->getFile(),
                    'linea' => $e->getLine(),
                    'trace' => $e->getTraceAsString()
                ]);
                $this->integrationLog($osinergmin_environment, 'PROCESS', 'ERROR', "Cliente {$client_name}: {$e->getMessage()}", $client_ocsa->id);

                $resu[] = [
                    'status' => 'ERROR',
                    'unit' => [],
                    'response' => [],
                    'bbdd' => false,
                    'error_message' => "Cliente {$client_name}: " . $e->getMessage()
                ];

                continue;
            }
        }
        $errors = collect($resu)->where('status', 'ERROR')->count();
        $successes = collect($resu)->where('status', 'SUCCESS')->count();
        $unknowns = collect($resu)->where('status', 'UNKNOWN')->count();
        $this->integrationLog(
            $osinergmin_environment,
            'RUN',
            $errors > 0 ? 'ERROR' : ($unknowns > 0 ? 'WARNING' : 'SUCCESS'),
            "Ejecución finalizada: {$successes} aceptados, {$errors} rechazados y {$unknowns} sin estado concluyente; {$clients_ocsa->count()} clientes evaluados."
        );

        return view('welcome', compact('resu', 'Date'));
        //return $resu;
    }

    private function providerStatus(array $item, array $response, int $httpStatus): string
    {
        $providerStatus = strtoupper(trim((string) ($item['status'] ?? $response['status'] ?? '')));

        if ($httpStatus === 202 || in_array($providerStatus, ['CREATED', 'ACCEPTED', 'SUCCESS', 'OK', 'PROCESSED', 'RECEIVED'], true)) {
            return 'SUCCESS';
        }

        if ($httpStatus < 200 || $httpStatus >= 300 || in_array($providerStatus, ['ERROR', 'REJECTED', 'FAILED', 'INVALID'], true)) {
            return 'ERROR';
        }

        $message = mb_strtolower($this->providerMessage($item, $this->providerMessage($response, '')));
        if ($message !== '' && preg_match('/success|created|accepted|procesad|recibid|registrad|aceptad/', $message)) {
            return 'SUCCESS';
        }
        if ($message !== '' && preg_match('/error|reject|invalid|rechaz|fall|deneg/', $message)) {
            return 'ERROR';
        }

        return 'UNKNOWN';
    }

    private function providerMessage(array $response, string $fallback): string
    {
        foreach (['message', 'detail', 'description', 'reason'] as $key) {
            if (isset($response[$key]) && is_scalar($response[$key]) && filled((string) $response[$key])) {
                return (string) $response[$key];
            }
        }

        if (isset($response['error'])) {
            if (is_scalar($response['error'])) {
                return (string) $response['error'];
            }
            if (is_array($response['error'])) {
                return $this->providerMessage($response['error'], $fallback);
            }
        }

        if (! empty($response['errors'])) {
            return mb_substr(json_encode($response['errors'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES), 0, 1000);
        }

        $raw = json_encode($response, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        return $response !== [] && $raw !== false ? mb_substr($raw, 0, 1000) : $fallback;
    }

    private function integrationLog(string $environment, string $stage, string $status, string $message, ?int $personId = null, ?int $httpStatus = null, array $context = []): void
    {
        try {
            IntegrationLog::create([
                'person_id' => $personId,
                'environment' => $environment,
                'stage' => $stage,
                'status' => $status,
                'http_status' => $httpStatus,
                'message' => mb_substr($message, 0, 4000),
                'context' => $context ? $this->sanitizeContext($context) : null,
            ]);
            app(\App\Services\IntegrationMailAlert::class)->handle($environment, $stage, $status, $message);
        } catch (\Throwable $exception) {
            Log::error('No se pudo registrar la bitácora de integración.', ['message' => $exception->getMessage()]);
        }
    }

    private function sanitizeContext(array $context): array
    {
        foreach ($context as $key => $value) {
            if (preg_match('/token|authorization|api[_-]?key|secret|password/i', (string) $key)) {
                $context[$key] = '[PROTEGIDO]';
            } elseif (is_array($value)) {
                $context[$key] = $this->sanitizeContext($value);
            }
        }

        return $context;
    }

    public function checkAndSendAlerts()
    {
        //$twilio = new TwilioService();
        $clients_ocsa = Person::operationalClients()->whereNotNull('token')
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

            $url = SystemConfig::ocsaBaseUrl() . config('services.ocsa.paths.alerts');

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
    //     $apiToken = config('services.whatsapp.token');

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
    //     $apiToken = config('services.whatsapp.token');

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
        $apiUrl = config('services.whatsapp.api_url');
        $apiToken = config('services.whatsapp.token');

        if (empty($apiUrl) || empty($apiToken)) {
            Log::warning('WhatsApp no está configurado; se omitió el envío.');
            return;
        }

        try {
            // Mensaje usando plantilla (en tu caso 'hello_world')
            $response = $client->post($apiUrl, [
                'json' => [
                    'messaging_product' => 'whatsapp',
                    'to' => $phoneNumber, //'51921502571',
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
                                        'text' => $message //'Prueba sin msg dinámico',
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
        $clients = Person::operationalClients()->whereNotNull('token')
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
            $url_units = SystemConfig::ocsaBaseUrl() . config('services.ocsa.paths.units');

            try {
                $response_units = $clientHttp->get($url_units, [
                    'query' => ['key' => $client_api],
                ]);
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
