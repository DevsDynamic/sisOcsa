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

        $response = $controller->sendDataOsinergmin();

        if ($response instanceof Response && $response->getStatusCode() === 409) {
            $this->warn('Se omitio la ejecucion porque otro envio sigue activo.');

            return self::SUCCESS;
        }

        $this->call('osinergmin:prune', [
            '--days' => 30,
            '--limit' => 1000,
        ]);

        $this->info('Envio a Osinergmin finalizado.');

        return self::SUCCESS;
    }
}
