<?php

namespace App\Console\Commands;

use App\Http\Controllers\TaskController;
use Illuminate\Console\Command;
use Symfony\Component\HttpFoundation\Response;

class SendOsinergminData extends Command
{
    protected $signature = 'osinergmin:send';

    protected $description = 'Consulta las unidades GPS y envia sus tramas a Osinergmin';

    public function handle(TaskController $controller): int
    {
        $this->info('Iniciando envio a Osinergmin...');

        try {
            $response = $controller->sendDataOsinergmin();
        } catch (\Throwable $exception) {
            $this->error('No se pudo ejecutar la retransmision: '.$exception->getMessage());

            return self::FAILURE;
        } finally {
            $this->callSilent('osinergmin:prune', ['--days' => 30, '--limit' => 1000]);
        }

        if ($response instanceof Response && $response->getStatusCode() === 409) {
            $this->warn('Se omitio la ejecucion porque otro envio sigue activo.');

            return self::SUCCESS;
        }

        $results = method_exists($response, 'getData')
            ? ($response->getData(true)['resu'] ?? [])
            : [];
        $successCount = collect($results)->where('status', 'SUCCESS')->count();
        $errorResults = collect($results)->whereIn('status', [
            'ERROR', 'WAF_BLOCKED', 'CONNECTION_ERROR', 'UNKNOWN',
        ]);

        foreach ($errorResults as $errorResult) {
            $this->error($errorResult['error_message'] ?? 'Error de envio sin detalle.');
        }

        if ($errorResults->isNotEmpty()) {
            $this->warn(
                "Envio finalizado con {$successCount} tramas exitosas y {$errorResults->count()} errores."
            );

            return self::FAILURE;
        }

        $this->info("Envio a Osinergmin finalizado: {$successCount} tramas exitosas.");

        return self::SUCCESS;
    }
}
