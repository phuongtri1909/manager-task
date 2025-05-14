<?php

namespace App\Models;

use App\Http\Traits\UseActiveScope;
use App\Http\Traits\UseAuth;
use App\Http\Traits\UseRoles;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, UseAuth, UseActiveScope, UseRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'email',
        'password',
        'department_id',
        'position_id',
        'unit_id',
        'role_id',
        'can_assign_job',
        'code_for_job_assignment',
        'co_tong_hop',
        'ma_chuc_vu',
        'ma_don_vi_cong_tac',
        'nguoi_nhap',
        'id_can_bo',
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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'can_assign_job' => 'boolean',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function wards(): BelongsToMany
    {
        return $this->belongsToMany(Ward::class, 'user_ward');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_user');
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'task_user')
            ->withPivot('status', 'viewed_at', 'completion_date', 'approved_by', 'approved_at')
            ->withTimestamps();
    }

    public function createdTasks(): HasMany
    {
        return $this->hasMany(Task::class, 'created_by');
    }

    public function taskExtensions(): HasMany
    {
        return $this->hasMany(TaskExtension::class);
    }

    public function requestedExtensions(): HasMany
    {
        return $this->hasMany(TaskExtension::class, 'requested_by');
    }

    public function approvedExtensions(): HasMany
    {
        return $this->hasMany(TaskExtension::class, 'approved_by');
    }

    public function adminlte_profile_url()
    {
        return 'profile';
    }
    
    public function permissionScoring(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_user');
    }
    
    public function permissionScoringIP(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_user');
    }
    
    public function permissionMroom(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_user');
    }

    public function isAdmin(): bool
    {
        return $this->role?->slug === 'admin';
    }

    public function isDirector(): bool
    {
        return $this->role?->slug === 'director';
    }

    public function isDeputyDirector(): bool
    {
        return $this->role?->slug === 'deputy-director';
    }

    public function isDepartmentHead(): bool
    {
        return $this->role?->slug === 'department-head';
    }

    public function isDeputyDepartmentHead(): bool
    {
        return $this->role?->slug === 'deputy-department-head';
    }

    public function isStaff(): bool
    {
        return $this->role?->slug === 'staff';
    }

    /**
     * Check if the user can assign tasks
     * 
     * @return bool
     */
    public function canAssignTasks(): bool
    {
        // Only admin always has task creation privileges, others need explicit permission
        return $this->can_assign_job || $this->isAdmin();
    }

    /**
     * Check if user belongs to a specific department with a role
     *
     * @param int $departmentId
     * @return bool
     */
    public function belongsToDepartment(int $departmentId): bool
    {
        return $this->department_id === $departmentId;
    }

    /**
     * Check if user has access to a department based on their role
     *
     * @param int $departmentId
     * @return bool
     */
    public function hasAccessToDepartment(int $departmentId): bool
    {
        // Admin, Director, Deputy Director have access to all departments
        if ($this->isAdmin() || $this->isDirector() || $this->isDeputyDirector()) {
            return true;
        }
        
        // Department-specific roles only have access to their own department
        return $this->belongsToDepartment($departmentId);
    }

    /**
     * Check if user has a specific role in a department
     *
     * @param string $roleSlug
     * @param int|null $departmentId
     * @return bool
     */
    public function hasRoleInDepartment(string $roleSlug, ?int $departmentId = null): bool
    {
        // First check if user has the role
        $userRoleSlug = $this->role?->slug;
        if ($userRoleSlug !== $roleSlug) {
            return false;
        }
        
        // If no department is specified, or the role is global, no need to check department
        if ($departmentId === null || $this->role?->isGlobal()) {
            return true;
        }
        
        // For department-specific roles, check if user belongs to the department
        return $this->belongsToDepartment($departmentId);
    }
}
