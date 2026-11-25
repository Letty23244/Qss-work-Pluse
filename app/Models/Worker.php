<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Worker extends Model
{
    use Auditable;

    protected $guarded = [];
    protected $casts = ['is_active' => 'boolean'];

    public function team() { return $this->belongsTo(Team::class); }
}
