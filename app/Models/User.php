<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasTenants;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Filament\Panel;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Filament\Facades\Filament;

class User extends Authenticatable implements FilamentUser, HasTenants
{
    use HasFactory, Notifiable, HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasVerifiedEmail();
    }

    /**
     * Tim (tenant) yang dimiliki atau diikuti oleh user ini.
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_user')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    /**
     * Mengembalikan daftar Team (Tenant) yang bisa diakses user ini.
     */
    public function getTenants(Panel $panel): Collection
    {
        return $this->teams;
    }

    /**
     * Mengecek apakah user boleh mengakses sebuah Tenant.
     */
    public function canAccessTenant(Model $tenant): bool
    {
        return $this->teams()->where('team_id', $tenant->id)->exists();
    }

    /**
     * Helper method profesional untuk mengecek role user
     * di tenant (team) yang sedang aktif.
     */
    public function hasTeamRole(string $role): bool
{
    $tenant = Filament::getTenant();

    if (!$tenant) {
        return false;
    }

    $teamPivot = $this->teams()->where('team_id', $tenant->id)->first();

    if (!$teamPivot) {
        return false;
    }

    return $teamPivot->pivot->role === $role;
}
}
