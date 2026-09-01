<?php

namespace App\Http\Controllers;

use App\Models\IntegrationLog;
use App\Models\Osinergmin;
use App\Services\SystemConfig;

class PublicIntegrationStatusController extends Controller
{
    public function __invoke()
    {
        $latestRun = IntegrationLog::where('environment', SystemConfig::environment())->where('stage', 'RUN')->latest('id')->first();
        $latestIds = Osinergmin::query()
            ->selectRaw('MAX(id)')
            ->where('environment', SystemConfig::environment())
            ->where('created_at', '>=', now()->subDays(30))
            ->whereNotNull('uuid')
            ->groupBy('uuid');
        $units = Osinergmin::query()->whereIn('id', $latestIds)
            ->select('uuid', 'plate', 'response_status', 'response_message', 'response_suggestion', 'created_at')
            ->orderByDesc('created_at')
            ->orderBy('plate')
            ->limit(200)->get();

        $summary = [
            'total' => $units->count(),
            'success' => $units->where('response_status', 'SUCCESS')->count(),
            'error' => $units->where('response_status', 'ERROR')->count(),
            'unknown' => $units->whereNotIn('response_status', ['SUCCESS', 'ERROR'])->count(),
        ];

        return view('integration-monitor.public-status', compact('latestRun', 'units', 'summary'));
    }
}
