<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Target extends Model
{
    use Auditable;

    protected $guarded = [];
    protected $casts = ['date' => 'date:Y-m-d', 'quantity' => 'decimal:2'];

    protected static function booted(): void
    {
        static::creating(function (Target $t) {
            $a = WorkAssignment::find($t->work_assignment_id);
            if ($a) {
                $t->client_id ??= $a->client_id;
                $t->activity  ??= $a->activity;
                $t->unit      ??= $a->unit;
            }
            $t->created_by ??= Auth::id();
        });

        // Keep a history of every change to the target quantity.
        static::updating(function (Target $t) {
            if ($t->isDirty('quantity')) {
                TargetAdjustment::create([
                    'target_id'    => $t->id,
                    'old_quantity' => $t->getOriginal('quantity'),
                    'new_quantity' => $t->quantity,
                    'reason'       => $t->last_adjustment_reason,
                    'adjusted_by'  => Auth::id(),
                ]);
            }
        });
    }

    public function assignment()  { return $this->belongsTo(WorkAssignment::class, 'work_assignment_id'); }
    public function client()      { return $this->belongsTo(Client::class); }
    public function adjustments() { return $this->hasMany(TargetAdjustment::class); }
}
