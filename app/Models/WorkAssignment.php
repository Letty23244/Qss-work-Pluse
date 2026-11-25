<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class WorkAssignment extends Model
{
    use Auditable;

    public const STATUSES = ['pending' => 'Pending', 'in_progress' => 'In progress', 'completed' => 'Completed'];

    protected $guarded = [];
    protected $casts = ['start_date' => 'date:Y-m-d', 'due_date' => 'date:Y-m-d'];

    protected static function booted(): void
    {
        static::creating(fn ($a) => $a->created_by ??= Auth::id());
    }

    public function client()  { return $this->belongsTo(Client::class); }
    public function team()    { return $this->belongsTo(Team::class); }
    public function targets() { return $this->hasMany(Target::class); }
    public function productionRecords() { return $this->hasMany(ProductionRecord::class); }

    public function totalProduced(): float
    {
        return (float) $this->productionRecords()->where('status', '!=', 'rejected')->sum('quantity_produced');
    }

    /** Keep assignment status in step with recorded production. */
    public function refreshStatus(): void
    {
        $produced = $this->totalProduced();
        $status = $produced <= 0 ? 'pending'
            : ($this->expected_output > 0 && $produced >= (float) $this->expected_output ? 'completed' : 'in_progress');

        if ($status !== $this->status) $this->update(['status' => $status]);
    }
}
