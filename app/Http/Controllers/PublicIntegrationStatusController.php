<?php

namespace App\Http\Controllers;

use App\Models\IntegrationLog;
use App\Models\Osinergmin;

class PublicIntegrationStatusController extends Controller
{
    public function __invoke()
    {
        $latestRun = IntegrationLog::where('stage', 'RUN')->latest('id')->first();
        $latestIds = Osinergmin::query()->selectRaw('MAX(id)')->whereNotNull('uuid')->groupBy('uuid');
        $units = Osinergmin::query()->whereIn('id', $latestIds)
            ->select('uuid', 'plate', 'response_status', 'response_timestamp', 'created_at')
            ->orderBy('plate')->limit(200)->get();

        return view('integration-monitor.public-status', compact('latestRun', 'units'));
    }
}
