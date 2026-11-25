<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{ProductionRecord, Target};
use App\Services\{PerformanceService, ReportExporter};
use Carbon\Carbon;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function __construct(protected PerformanceService $svc) {}

    /** GET /client/dashboard?date= */
    public function dashboard(Request $request)
    {
        $f = $this->filters($request);

        return [
            'filters'     => $f,
            'summary'     => $this->svc->summary($f),
            'by_shift'    => $this->svc->breakdown('shift_type', $f),
            'by_team'     => $this->svc->breakdown('team_id', $f),
            'assignments' => $this->svc->assignmentProgress($f['client_id']),
            'recent'      => ProductionRecord::with(['assignment:id,title,unit', 'team:id,name', 'supervisor:id,name'])
                ->when($f['client_id'], fn ($q, $c) => $q->where('client_id', $c))
                ->latest('submitted_at')->limit(10)->get(),
        ];
    }

    /** GET /client/reports/{type}  type = daily|shift|team|assignment|weekly|monthly */
    public function report(Request $request, string $type)
    {
        [$by, $f] = $this->resolve($request, $type);

        return [
            'type' => $type, 'filters' => $f,
            'summary' => $this->svc->summary($f),
            'rows'    => $this->svc->breakdown($by, $f),
        ];
    }

    /** GET /client/reports/{type}/export?format=pdf|xlsx */
    public function export(Request $request, string $type, ReportExporter $exporter)
    {
        [$by, $f] = $this->resolve($request, $type);

        return $request->query('format', 'pdf') === 'xlsx'
            ? $exporter->excel($by, $f)
            : $exporter->pdf($by, $f);
    }

    /** GET /client/production-records */
    public function records(Request $request)
    {
        $f = $this->filters($request);

        return ProductionRecord::with(['assignment:id,title,unit', 'team:id,name', 'supervisor:id,name'])
            ->when($f['client_id'], fn ($q, $c) => $q->where('client_id', $c))
            ->whereDate('date', '>=', $f['from'])->whereDate('date', '<=', $f['to'])
            ->where('status', '!=', 'rejected')
            ->orderByDesc('date')->latest('submitted_at')->paginate(25);
    }

    /** GET /client/assignments */
    public function assignments(Request $request)
    {
        return $this->svc->assignmentProgress($this->filters($request)['client_id']);
    }

    /** GET /client/targets */
    public function targets(Request $request)
    {
        $f = $this->filters($request);

        return Target::with('assignment:id,title,unit')
            ->when($f['client_id'], fn ($q, $c) => $q->where('client_id', $c))
            ->whereDate('date', '>=', $f['from'])->whereDate('date', '<=', $f['to'])
            ->orderBy('date')->get();
    }

    protected function resolve(Request $request, string $type): array
    {
        $f = $this->filters($request);

        return match ($type) {
            'daily'      => ['date', $f],
            'shift'      => ['shift_type', $f],
            'team'       => ['team_id', $f],
            'assignment' => ['work_assignment_id', $f],
            'weekly'     => ['date', $this->period($f, 'Week')],
            'monthly'    => ['date', $this->period($f, 'Month')],
            default      => abort(404, 'Unknown report type.'),
        };
    }

    protected function period(array $f, string $unit): array
    {
        $d = Carbon::parse($f['from']);

        return [
            'from' => $d->copy()->{"startOf$unit"}()->toDateString(),
            'to'   => $d->copy()->{"endOf$unit"}()->toDateString(),
        ] + $f;
    }

    /** Clients are locked to their own company; admins may pass ?client_id= */
    protected function filters(Request $request): array
    {
        $user = $request->user();
        $client = $user->isAdmin() ? $request->query('client_id') : $user->client_id;

        abort_if(! $user->isAdmin() && ! $client, 403, 'No client company is linked to this account.');

        $from = $request->query('from', $request->query('date', today()->toDateString()));

        return [
            'client_id' => $client ? (int) $client : null,
            'from'      => $from,
            'to'        => $request->query('to', $from),
        ];
    }
}
