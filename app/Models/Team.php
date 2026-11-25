<?php

namespace App\Models;

use App\Traits\Auditable;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use Auditable;

    protected $guarded = [];
    protected $casts = ['is_active' => 'boolean'];

    public function client()     { return $this->belongsTo(Client::class); }
    public function supervisor() { return $this->belongsTo(User::class, 'supervisor_id'); }
    public function workers()    { return $this->hasMany(Worker::class); }
    public function shifts()     { return $this->hasMany(Shift::class); }
}
