<?php

namespace App\Models;

use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    private static bool $resolvingGhost = false;

    protected static function booted(): void
    {
        // Ghost users are invisible to everyone except themselves
        static::addGlobalScope('not_ghost', function ($query) {
            // Prevent infinite recursion: auth()->user() triggers another User query
            if (static::$resolvingGhost) {
                return;
            }

            static::$resolvingGhost = true;
            $loggedIn = auth()->user();
            static::$resolvingGhost = false;

            // During auth (login), no user is resolved yet — allow all
            if (! $loggedIn) {
                return;
            }
            // Ghost user can see themselves
            if ($loggedIn->is_ghost) {
                return;
            }
            // Everyone else: hide ghosts
            $query->where('is_ghost', false);
        });
    }

    protected $fillable = [
        'emp_id',
        'name',
        'email',
        'password',
        'role',
        'designation',
        'department',
        'contact_number',
        'hub_id',
        'is_active',
        'is_ghost',
        'meta',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
            'meta' => 'array',
        ];
    }

    public function hub(): BelongsTo
    {
        return $this->belongsTo(Hub::class);
    }

    public function isHead(): bool
    {
        return $this->role === UserRole::Head;
    }

    public function isHubCoordinator(): bool
    {
        return $this->role === UserRole::HubCoordinator;
    }

    public function isLawyer(): bool
    {
        return $this->role === UserRole::Lawyer;
    }

    public function isCourtClerk(): bool
    {
        return $this->role === UserRole::CourtClerk;
    }

    public function isOperationsOfficer(): bool
    {
        return $this->role === UserRole::OperationsOfficer;
    }

    public function isViewer(): bool
    {
        return $this->role === UserRole::Viewer;
    }

    public function canSeeAllHubs(): bool
    {
        $scope = $this->role->isGlobalScope();
        if ($scope === true) return true;
        if ($scope === false) return false;
        // null = depends on hub_id
        return is_null($this->hub_id);
    }

    public function canWrite(): bool
    {
        return $this->role->canWrite();
    }

    public function effectiveHubId(): ?string
    {
        if ($this->canSeeAllHubs()) {
            return session('active_hub', null);
        }
        return $this->hub_id;
    }
}
