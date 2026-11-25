<?php

namespace App\Models;

use App\Notifications\ProductionSubmitted;
use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Notification;

class ProductionRecord extends Model
{
    use Auditable;

    public const STATUSES = ['submitted' => 'Submitted', 'approved' => 'Approved', 'rejected' => 'Rejected'];

    protected $guarded = [];
    protected $casts = [
        'date'              => 'date:Y-m-d',
        'quantity_produced' => 'decimal:2',
        'quantity_pending'  => 'decimal:2',
        'submitted_at'      => 'datetime',
        'approved_at'       => 'datetime',
    ];

    protected static function booted(): void
    {
        // Everything links back to the shift: client, team, supervisor, date.
        static::creating(function (ProductionRecord $r) {
            if ($s = Shift::find($r->shift_id)) {
                $r->client_id     = $s->client_id;
                $r->team_id       = $s->team_id;
                $r->supervisor_id = $s->supervisor_id;
                $r->date          = $s->date;
                $r->shift_type    = $s->shift_type;
            }
            $r->submitted_at ??= now();
        });

        static::created(function (ProductionRecord $r) {
            $recipients = User::where('is_active', true)
                ->where(fn ($q) => $q->where('role', 'admin')->orWhere('client_id', $r->client_id))
                ->get();
            Notification::send($recipients, new ProductionSubmitted($r));
        });

        // Keep the work assignment status in step with recorded production.
        static::saved(fn (ProductionRecord $r) => $r->assignment?->refreshStatus());
    }

    public function shift()      { return $this->belongsTo(Shift::class); }
    public function assignment() { return $this->belongsTo(WorkAssignment::class, 'work_assignment_id'); }
    public function client()     { return $this->belongsTo(Client::class); }
    public function team()       { return $this->belongsTo(Team::class); }
    public function supervisor() { return $this->belongsTo(User::class, 'supervisor_id'); }
    public function approver()   { return $this->belongsTo(User::class, 'approved_by'); }
}
