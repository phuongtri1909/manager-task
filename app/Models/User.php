<?php

namespace App\Models;

use App\Http\Traits\UseActiveScope;
use App\Http\Traits\UseAuth;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

use App\Http\Traits\UseRoles;

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
        'can_assign_job',
        'code_for_job_assignment',
        'co_tong_hop',
        'ma_chuc_vu',
        'ma_don_vi_cong_tac',
        'nguoi_nhap',
        'id_can_bo',
        'role_id',
        'can_assign_task',
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


    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
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

    /**
     * Get tasks assigned to the user
     */
    public function assignedTasks()
    {
        return $this->tasks();
    }

    /**
     * Get unread tasks for the user
     */
    public function unreadTasks()
    {
        return $this->tasks()->wherePivotNull('viewed_at');
    }

    /**
     * Get tasks with pending status
     */
    public function pendingTasks()
    {
        return $this->tasks()->wherePivot('status', Task::STATUS_PENDING);
    }

    /**
     * Get tasks with in-progress status
     */
    public function inProgressTasks()
    {
        return $this->tasks()->wherePivot('status', Task::STATUS_IN_PROGRESS);
    }

    /**
     * Get completed tasks
     */
    public function completedTasks()
    {
        return $this->tasks()->wherePivot('status', Task::STATUS_COMPLETED);
    }

    /**
     * Get approved tasks
     */
    public function approvedTasks()
    {
        return $this->tasks()->wherePivot('status', Task::STATUS_APPROVED);
    }

    /**
     * Get rejected tasks
     */
    public function rejectedTasks()
    {
        return $this->tasks()->wherePivot('status', Task::STATUS_REJECTED);
    }

    /**
     * Mark a task as viewed by this user
     */
    public function markTaskAsViewed(Task $task): void
    {
        $task->markAsViewedByUser($this);
    }

    /**
     * Update task status for this user
     */
    public function updateTaskStatus(Task $task, string $status): void
    {
        $task->updateStatusForUser($this, $status);
    }

    /**
     * Add a comment to a task
     */
    public function commentOnTask(Task $task, string $comment, ?string $statusUpdate = null): TaskComment
    {
        return $task->addComment($this, $comment, $statusUpdate);
    }

    /**
     * Add a comment with attachments to a task
     */
    public function commentOnTaskWithAttachments(Task $task, string $comment, array $attachments, ?string $statusUpdate = null): TaskComment
    {
        return $task->addCommentWithAttachments($this, $comment, $attachments, $statusUpdate);
    }

    /**
     * Update task status with a comment and optional attachments
     */
    public function updateTaskStatusWithComment(Task $task, string $status, string $comment, array $attachments = []): void
    {
        $task->updateStatusForUserWithComment($this, $status, $comment, $attachments);
    }

    /**
     * Get task comments made by this user
     */
    public function taskComments(): HasMany
    {
        return $this->hasMany(TaskComment::class);
    }

    /**
     * Get task attachments uploaded by this user
     */
    public function taskAttachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class, 'uploaded_by');
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
        return $this->can_assign_task;
    }

    public function canAssignTasksAdmin(): bool
    {
        // Only admin always has task creation privileges, others need explicit permission
        return $this->can_assign_task || $this->isAdmin();
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

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
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
}
