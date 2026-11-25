<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use Auditable;

    public const TYPES = ['morning' => 'Morning', 'afternoon' => 'Afternoon', 'night' => 'Night'];
    public const STATUSES = ['scheduled' => 'Scheduled', 'active' => 'Active', 'completed' => 'Completed'];

    protected $guarded = [];
    protected $casts = ['date' => 'date:Y-m-d'];

    public function client()     { return $this->belongsTo(Client::class); }
    public function team()       { return $this->belongsTo(Team::class); }
    public function supervisor() { return $this->belongsTo(User::class, 'supervisor_id'); }
    public function productionRecords() { return $this->hasMany(ProductionRecord::class); }

    public function getLabelAttribute(): string
    {
        return $this->date->format('d M Y').' · '.ucfirst($this->shift_type).' · '.($this->team?->name ?? '—');
    }
}
