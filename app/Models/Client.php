<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use Auditable;

    protected $guarded = [];
    protected $casts = ['is_active' => 'boolean'];

    public function users()   { return $this->hasMany(User::class); }
    public function teams()   { return $this->hasMany(Team::class); }
    public function shifts()  { return $this->hasMany(Shift::class); }
    public function assignments() { return $this->hasMany(WorkAssignment::class); }
    public function targets() { return $this->hasMany(Target::class); }
    public function productionRecords() { return $this->hasMany(ProductionRecord::class); }
}
