<?php

namespace App\Http\Controllers;

use App\Models\Person;
use App\Models\Osinergmin;
use App\Services\SystemConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user = request()->user();
        $maySeeAll = $user->is_system_owner || $user->can('osinergmins.manage');
        $personId = $user->person?->id;
        $environment = SystemConfig::environment();
        $clientQuery = Person::operationalClients()
            ->when(! $maySeeAll, fn ($query) => $personId
                ? $query->whereKey($personId)
                : $query->whereRaw('1 = 0'));
        $clients = (clone $clientQuery)->count();
        $activeClients = (clone $clientQuery)->where('status', true)->count();
        $inactiveClients = $clients - $activeClients;
        $gpsSources = Person::activeGpsSources()->count();
        $transmissions = Osinergmin::query()->forEnvironment($environment)->visibleTo($user);
        $today = (clone $transmissions)->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])->count();
        $errorsToday = (clone $transmissions)->whereBetween('created_at', [now()->startOfDay(), now()->endOfDay()])->where('response_status', 'ERROR')->count();
        $lastTransmission = (clone $transmissions)->latest('id')->first();
        $daily = (clone $transmissions)->selectRaw('DATE(created_at) day, COUNT(*) total, SUM(response_status = ?) success', ['SUCCESS'])
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())->groupBy(DB::raw('DATE(created_at)'))->orderBy('day')->get();

        $todayReportUrl = route('reports.osinergmin', ['from' => now()->toDateString(), 'to' => now()->toDateString()]);
        $todayErrorsUrl = route('reports.osinergmin', ['from' => now()->toDateString(), 'to' => now()->toDateString(), 'status' => 'ERROR']);

        return view('dashboard', compact('clients', 'activeClients', 'inactiveClients', 'gpsSources', 'today', 'errorsToday', 'lastTransmission', 'daily', 'environment', 'todayReportUrl', 'todayErrorsUrl'));
    }

    public function status(Request $request)
    {
        $user = $request->user();
        $environment = SystemConfig::environment();

        return response()->json(Cache::remember("dashboard:status:{$environment}:{$user->id}", now()->addSeconds(30), function () use ($user, $environment) {
            $transmissions = Osinergmin::query()->forEnvironment($environment)->visibleTo($user);
            $todayRange = [now()->startOfDay(), now()->endOfDay()];
            $last = (clone $transmissions)->latest('id')->first(['created_at', 'response_status']);

            return [
                'today' => (clone $transmissions)->whereBetween('created_at', $todayRange)->count(),
                'errors_today' => (clone $transmissions)->whereBetween('created_at', $todayRange)
                    ->whereIn('response_status', ['ERROR', 'REJECTED', 'FAILED'])->count(),
                'last_transmission_at' => $last?->created_at?->toIso8601String(),
                'last_status' => $last?->response_status,
            ];
        }));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
