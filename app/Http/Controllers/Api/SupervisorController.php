<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{ProductionRecord, Shift, WorkAssignment};
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SupervisorController extends Controller
{
    /** GET /supervisor/shifts?date=Y-m-d */
    public function shifts(Request $request)
    {
        $user = $request->user();

        return Shift::with(['client:id,name', 'team:id,name'])
            ->when(! $user->isAdmin(), fn ($q) => $q->where('supervisor_id', $user->id))
            ->whereDate('date', $request->query('date', today()->toDateString()))
            ->orderBy('start_time')->get();
    }

    /** GET /supervisor/shifts/{shift} – shift + the work the supervisor can report on */
    public function show(Request $request, Shift $shift)
    {
        $this->own($request, $shift);

        return [
            'shift'       => $shift->load(['client:id,name', 'team:id,name', 'productionRecords.assignment:id,title,unit']),
            'assignments' => WorkAssignment::where('client_id', $shift->client_id)
                ->where('team_id', $shift->team_id)->where('status', '!=', 'completed')
                ->with(['targets' => fn ($q) => $q->whereDate('date', $shift->date)
                    ->where(fn ($w) => $w->whereNull('shift_type')->orWhere('shift_type', $shift->shift_type))])
                ->get(),
        ];
    }

    public function start(Request $request, Shift $shift)
    {
        $this->own($request, $shift);
        $this->transition($shift, 'scheduled', 'active');
        return $shift->fresh();
    }

    public function complete(Request $request, Shift $shift)
    {
        $this->own($request, $shift);
        $this->transition($shift, 'active', 'completed');
        return $shift->fresh();
    }

    /** POST /supervisor/production-records */
    public function store(Request $request)
    {
        $data = $request->validate([
            'shift_id'           => ['required', 'exists:shifts,id'],
            'work_assignment_id' => ['required', 'exists:work_assignments,id'],
            'quantity_produced'  => ['required', 'numeric', 'min:0'],
            'quantity_pending'   => ['nullable', 'numeric', 'min:0'],
            'remarks'            => ['nullable', 'string', 'max:1000'],
        ]);

        $shift = Shift::findOrFail($data['shift_id']);
        $this->own($request, $shift);
        $a = WorkAssignment::findOrFail($data['work_assignment_id']);

        if ($shift->status === 'scheduled') {
            $this->fail('shift_id', 'Start the shift before submitting production.');
        }
        if ($a->client_id !== $shift->client_id || $a->team_id !== $shift->team_id) {
            $this->fail('work_assignment_id', 'This work assignment does not belong to the shift\'s client and team.');
        }
        if (ProductionRecord::where('shift_id', $shift->id)->where('work_assignment_id', $a->id)->exists()) {
            $this->fail('work_assignment_id', 'Production for this assignment was already submitted for this shift. Update it instead.');
        }

        $record = ProductionRecord::create($data + ['quantity_pending' => $data['quantity_pending'] ?? 0]);

        return response()->json($record->load(['assignment:id,title,unit', 'team:id,name']), 201);
    }

    /** PUT /supervisor/production-records/{record} – allowed until it is approved */
    public function update(Request $request, ProductionRecord $record)
    {
        abort_unless($request->user()->isAdmin() || $record->supervisor_id === $request->user()->id, 403);

        if ($record->status === 'approved') {
            $this->fail('status', 'Approved records cannot be edited.');
        }

        $data = $request->validate([
            'quantity_produced' => ['required', 'numeric', 'min:0'],
            'quantity_pending'  => ['nullable', 'numeric', 'min:0'],
            'remarks'           => ['nullable', 'string', 'max:1000'],
        ]);

        $record->update($data + ['status' => 'submitted']);

        return $record->fresh();
    }

    /** GET /supervisor/production-records */
    public function records(Request $request)
    {
        $user = $request->user();

        return ProductionRecord::with(['assignment:id,title,unit', 'team:id,name'])
            ->when(! $user->isAdmin(), fn ($q) => $q->where('supervisor_id', $user->id))
            ->when($request->query('date'), fn ($q, $d) => $q->whereDate('date', $d))
            ->latest('submitted_at')->paginate(25);
    }

    protected function own(Request $request, Shift $shift): void
    {
        abort_unless($request->user()->isAdmin() || $shift->supervisor_id === $request->user()->id, 403, 'This shift is not assigned to you.');
    }

    protected function transition(Shift $shift, string $from, string $to): void
    {
        if ($shift->status !== $from) $this->fail('status', "Shift must be $from to move to $to.");
        $shift->update(['status' => $to]);
    }

    protected function fail(string $field, string $msg): never
    {
        throw ValidationException::withMessages([$field => [$msg]]);
    }
}
