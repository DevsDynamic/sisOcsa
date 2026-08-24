<?php

namespace App\Console\Commands;

use App\Models\Osinergmin;
use Illuminate\Console\Command;

class PruneOsinergminData extends Command
{
    protected $signature = 'osinergmin:prune
        {--days=30 : Dias de informacion que se conservaran}
        {--limit=1000 : Maximo de filas que se eliminaran por ejecucion}';

    protected $description = 'Elimina retransmisiones antiguas de Osinergmin por bloques';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $limit = max(1, (int) $this->option('limit'));
        $cutoff = now()->subDays($days);
        $deleted = 0;

        do {
            $batchSize = min(1000, $limit - $deleted);
            $ids = Osinergmin::query()
                ->where('created_at', '<', $cutoff)
                ->orderBy('id')
                ->limit($batchSize)
                ->pluck('id');

            if ($ids->isEmpty()) {
                break;
            }

            $deleted += Osinergmin::whereIn('id', $ids)->delete();
        } while ($deleted < $limit);

        $this->info("Se eliminaron {$deleted} retransmisiones anteriores a {$cutoff->toDateTimeString()}.");

        return self::SUCCESS;
    }
}
