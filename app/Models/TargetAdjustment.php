<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TargetAdjustment extends Model
{
    protected $guarded = [];

    public function target() { return $this->belongsTo(Target::class); }
    public function adjuster() { return $this->belongsTo(User::class, 'adjusted_by'); }
}
