<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\URL;

class IntegrationStatusLink extends Command
{
    protected $signature = 'integration:status-link {--hours=0 : Horas de vigencia; 0 genera un enlace sin vencimiento}';
    protected $description = 'Genera un enlace firmado y de solo lectura al estado de integración';

    public function handle(): int
    {
        $hours = max(0, (int) $this->option('hours'));
        $url = $hours > 0
            ? URL::temporarySignedRoute('integration-status.public', now()->addHours($hours))
            : URL::signedRoute('integration-status.public');
        $this->line($url);
        return self::SUCCESS;
    }
}
