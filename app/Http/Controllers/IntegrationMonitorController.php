<?php

namespace App\Http\Controllers;

use App\Models\IntegrationLog;
use App\Models\Osinergmin;
use App\Models\Person;
use App\Services\SystemConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class IntegrationMonitorController extends Controller
{
    public function index()
    {
        $latestRun = IntegrationLog::where('stage', 'RUN')->latest('id')->first();
        $lastSuccess = IntegrationLog::where('stage', 'RUN')->where('status', 'SUCCESS')->latest('id')->first();
        $recentErrors = IntegrationLog::with('person')->where('status', 'ERROR')->latest('id')->limit(20)->get();
        $recentRejections = Osinergmin::with('person')
            ->where('environment', SystemConfig::environment())
            ->where('response_status', 'ERROR')
            ->latest('id')
            ->limit(25)
            ->get();
        $logs = IntegrationLog::with('person')->latest('id')->paginate(50);
        $environment = SystemConfig::environment();
        $cronStale = !$latestRun || $latestRun->created_at->lt(now()->subMinutes(5));
        $demoClients = Person::where('is_demo', true)->count();
        $demoRecords = Osinergmin::where('environment', 'development')->count();
        $publicStatusUrl = URL::temporarySignedRoute('integration-status.public', now()->addDay());

        return view('integration-monitor.index', compact('latestRun', 'lastSuccess', 'recentErrors', 'recentRejections', 'logs', 'environment', 'cronStale', 'demoClients', 'demoRecords', 'publicStatusUrl'));
    }

    public function purgeDemo(Request $request)
    {
        $request->validate(['confirmation' => ['required', 'in:ELIMINAR DEMO']]);

        $deleted = DB::transaction(function () {
            $logs = IntegrationLog::where('environment', 'development')->delete();
            $records = Osinergmin::where('environment', 'development')->delete();
            $clients = Person::where('is_demo', true)->delete();
            return compact('logs', 'records', 'clients');
        });

        return back()->with('status', "Limpieza completada: {$deleted['clients']} clientes, {$deleted['records']} retransmisiones y {$deleted['logs']} eventos demo eliminados.");
    }

    public function sendNow(Request $request, TaskController $taskController)
    {
        $validated = $request->validate(['environment' => ['required', 'in:development,production']]);

        try {
            $response = $taskController->sendDataOsinergmin($validated['environment']);
            if (method_exists($response, 'getStatusCode') && $response->getStatusCode() === 409) {
                return back()->with('warning', 'No se inició: existe otra retransmisión en curso.');
            }
            $results = method_exists($response, 'getData') ? ($response->getData(true)['resu'] ?? []) : [];
            $success = collect($results)->where('status', 'SUCCESS')->count();
            $errors = collect($results)->where('status', 'ERROR')->count();
            $blocked = collect($results)->where('status', 'WAF_BLOCKED')->count();
            $unknown = collect($results)->where('status', 'UNKNOWN')->count();
            $connectionErrors = collect($results)->where('status', 'CONNECTION_ERROR')->count();
            return back()->with(($errors || $blocked || $connectionErrors || $unknown) ? 'warning' : 'status', "Ejecución manual finalizada: {$success} aceptados, {$errors} rechazados por la API, {$blocked} bloqueados por el firewall, {$connectionErrors} errores de conexión y {$unknown} sin estado concluyente. Revisa la bitácora.");
        } catch (\Throwable $exception) {
            return back()->with('warning', 'La ejecución no pudo completarse: '.$exception->getMessage());
        }
    }
}
