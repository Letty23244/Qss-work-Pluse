<?php

namespace App\Models;

use App\Traits\Auditable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable, Auditable;

    public const ROLES = ['admin' => 'Administrator', 'supervisor' => 'Supervisor', 'client' => 'Client'];

    protected $fillable = ['name', 'email', 'password', 'role', 'phone', 'client_id', 'is_active'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'is_active'         => 'boolean',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role === 'admin' && $this->is_active;
    }

    public function isAdmin(): bool      { return $this->role === 'admin'; }
    public function isSupervisor(): bool { return $this->role === 'supervisor'; }
    public function isClient(): bool     { return $this->role === 'client'; }

    public function client() { return $this->belongsTo(Client::class); }
    public function supervisedTeams() { return $this->hasMany(Team::class, 'supervisor_id'); }
}
