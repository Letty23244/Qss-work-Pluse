<?php

namespace App\Services;

use App\Models\{Client, Shift, Team, WorkAssignment};
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

/**
 * All productivity maths lives here so the API, Filament pages,
 * widgets and exports always show the same numbers.
 * Rejected production records are ignored.
 */
class PerformanceService
{
    public const GROUPS = ['date', 'shift_type', 'team_id', 'client_id', 'work_assignment_id'];

    /** filters: client_id?, from?, to? (Y-m-d) */
    public function summary(array $f): array
    {
        $p = $this->production($f)
            ->selectRaw('COALESCE(SUM(quantity_produced),0) as produced, COALESCE(SUM(quantity_pending),0) as pending')
            ->first();
        $target = (float) $this->targets($f)->sum('targets.quantity');

        return $this->shape($target, (float) $p->produced, (float) $p->pending);
    }

    public function breakdown(string $by, array $f): array
    {
        abort_unless(in_array($by, self::GROUPS, true), 422, 'Invalid grouping.');

        $pCol = $by === 'date' ? 'DATE(production_records.date)' : "production_records.$by";
        $prod = $this->production($f)
            ->selectRaw("$pCol as k, SUM(quantity_produced) as produced, SUM(quantity_pending) as pending")
            ->groupByRaw($pCol)->get()->keyBy(fn ($r) => (string) $r->k);

        $t = $this->targets($f);
        if ($by === 'team_id') {
            $t->join('work_assignments', 'work_assignments.id', '=', 'targets.work_assignment_id')
              ->selectRaw('work_assignments.team_id as k, SUM(targets.quantity) as target')
              ->groupBy('work_assignments.team_id');
        } else {
            $tCol = $by === 'date' ? 'DATE(targets.date)' : "targets.$by";
            $t->selectRaw("$tCol as k, SUM(targets.quantity) as target")->groupByRaw($tCol);
        }
        $tgt = $t->get()->keyBy(fn ($r) => (string) $r->k);

        $labels = $this->labels($by);

        return $prod->keys()->merge($tgt->keys())->unique()->sort()->values()->map(function ($k) use ($prod, $tgt, $labels) {
            return $this->shape(
                (float) ($tgt[$k]->target ?? 0),
                (float) ($prod[$k]->produced ?? 0),
                (float) ($prod[$k]->pending ?? 0),
                ['key' => $k, 'label' => $labels[$k] ?? ($k === '' ? 'Whole day (unallocated)' : $k)]
            );
        })->all();
    }

    /** Progress of every work assignment: expected vs produced vs remaining. */
    public function assignmentProgress(?int $clientId = null): array
    {
        return WorkAssignment::with(['team:id,name', 'client:id,name'])
            ->when($clientId, fn ($q) => $q->where('client_id', $clientId))
            ->orderByDesc('start_date')->get()
            ->map(function (WorkAssignment $a) {
                $produced = $a->totalProduced();
                $expected = (float) $a->expected_output;
                return [
                    'id' => $a->id, 'title' => $a->title, 'activity' => $a->activity, 'unit' => $a->unit,
                    'client' => $a->client?->name, 'team' => $a->team?->name, 'status' => $a->status,
                    'expected_output' => $expected, 'produced' => round($produced, 2),
                    'remaining' => round(max($expected - $produced, 0), 2),
                    'achievement' => $expected > 0 ? round($produced / $expected * 100, 2) : null,
                    'start_date' => $a->start_date?->toDateString(), 'due_date' => $a->due_date?->toDateString(),
                ];
            })->all();
    }

    protected function shape(float $target, float $produced, float $pending, array $extra = []): array
    {
        return $extra + [
            'target'      => round($target, 2),
            'produced'    => round($produced, 2),
            'pending'     => round($pending, 2),
            'remaining'   => round(max($target - $produced, 0), 2),
            'achievement' => $target > 0 ? round($produced / $target * 100, 2) : null,
        ];
    }

    protected function production(array $f): Builder
    {
        return DB::table('production_records')
            ->where('status', '!=', 'rejected')
            ->when($f['client_id'] ?? null, fn ($q, $v) => $q->where('production_records.client_id', $v))
            ->when($f['from'] ?? null, fn ($q, $v) => $q->whereDate('production_records.date', '>=', $v))
            ->when($f['to'] ?? null, fn ($q, $v) => $q->whereDate('production_records.date', '<=', $v));
    }

    protected function targets(array $f): Builder
    {
        return DB::table('targets')
            ->when($f['client_id'] ?? null, fn ($q, $v) => $q->where('targets.client_id', $v))
            ->when($f['from'] ?? null, fn ($q, $v) => $q->whereDate('targets.date', '>=', $v))
            ->when($f['to'] ?? null, fn ($q, $v) => $q->whereDate('targets.date', '<=', $v));
    }

    protected function labels(string $by): array
    {
        return match ($by) {
            'team_id'            => Team::pluck('name', 'id')->all(),
            'client_id'          => Client::pluck('name', 'id')->all(),
            'work_assignment_id' => WorkAssignment::pluck('title', 'id')->all(),
            'shift_type'         => Shift::TYPES,
            default              => [],
        };
    }
}
