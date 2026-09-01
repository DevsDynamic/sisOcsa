<?php

namespace App\Http\Controllers;

use App\Exports\OsinergminExport;
use App\Models\Osinergmin;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;
use App\Services\SystemConfig;

class ReportController extends Controller
{
    public function index()
    {
        return redirect()->route('reports.osinergmin');
    }

    public function getRetransmissionsReport(Request $request)
    {
        [$from, $to] = $this->validatedRange($request);

        $retransmissions = Osinergmin::query()
            ->forEnvironment()
            ->visibleTo($request->user())
            ->whereBetween('created_at', [$from, $to])
            ->latest('id')
            ->limit(500)
            ->get();

        $html = view('reports.partials.retransmissions_report', compact('retransmissions'))->render();

        return response()->json(['html' => $html]);
    }

    public function reportOsinergmin(Request $request)
    {
        // El reporte se alimenta de la base local y no se bloquea si OCSA cae.
        $unitOptions = Osinergmin::query()
            ->forEnvironment()
            ->visibleTo($request->user())
            ->select('uuid', 'plate')
            ->whereNotNull('uuid')
            ->where('uuid', '<>', '')
            ->distinct()
            ->orderBy('plate')
            ->get()
            ->map(fn (Osinergmin $row) => [
                'id' => $row->uuid,
                'plate' => $row->plate ?: $row->uuid,
            ]);

        return view('reports.osinergmin', compact('unitOptions'));
    }

    public function viewReportOsinergmin(Request $request)
    {
        [$from, $to] = $this->validatedRange($request);

        $query = Osinergmin::query()
            ->forEnvironment()
            ->visibleTo($request->user())
            ->select([
                'id', 'uuid', 'plate', 'event', 'speed', 'latitude', 'longitude',
                'gpsDate', 'odometer', 'response_timestamp', 'response_message',
                'response_suggestion', 'response_status', 'created_at', 'updated_at',
            ])
            ->whereBetween('created_at', [$from, $to])
            ->when($request->filled('unit'), fn ($query) => $query->where('uuid', $request->string('unit')))
            ->when($request->filled('status'), fn ($query) => $query->where('response_status', $request->string('status')))
            ->orderByDesc('id');

        return DataTables::eloquent($query)
            ->addIndexColumn()
            ->editColumn('gpsDate', fn (Osinergmin $row) => $row->gpsDate ?: '-')
            ->editColumn('response_timestamp', fn (Osinergmin $row) => $row->response_timestamp ?: '-')
            ->make(true);
    }

    public function exportOsinergmin(Request $request)
    {
        [$from, $to] = $this->validatedRange($request);
        $unit = $request->filled('unit') ? $request->string('unit')->toString() : null;
        $status = $request->filled('status') ? $request->string('status')->toString() : null;

        $user = $request->user();
        $maySeeAll = $user->is_system_owner || $user->can('osinergmins.manage');

        return Excel::download(new OsinergminExport(
            $unit,
            $from,
            $to,
            $status,
            SystemConfig::environment(),
            $maySeeAll ? null : $user->person?->id,
            $maySeeAll
        ), 'osinergmin.xlsx');
    }

    private function validatedRange(Request $request): array
    {
        $validated = $request->validate([
            'from' => ['required', 'date_format:Y-m-d'],
            'to' => ['required', 'date_format:Y-m-d', 'after_or_equal:from'],
            'unit' => ['nullable', 'string', 'max:255'],
            'status' => ['nullable', 'in:SUCCESS,ERROR'],
        ]);

        $from = Carbon::createFromFormat('Y-m-d', $validated['from'])->startOfDay();
        $to = Carbon::createFromFormat('Y-m-d', $validated['to'])->endOfDay();

        if ($from->diffInDays($to) > 31) {
            throw ValidationException::withMessages([
                'to' => 'El rango máximo permitido es de 31 días.',
            ]);
        }

        return [$from, $to];
    }
}
