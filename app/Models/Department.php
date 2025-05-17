<?php

namespace App\Models;

use App\Http\Traits\UseActiveScope;
use App\Http\Traits\UseAuth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\DepartmentBranch;
use App\Models\Branch;

class Department extends Model
{
    use HasFactory, SoftDeletes, UseActiveScope, UseAuth;

    protected $fillable = [
        'parent_id',
        'slug',
        'name',
        'level',
        'active',
        'created_by',
        'updated_by',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'department_permission');
    }

    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'department_task')
            ->withPivot('status', 'viewed_at')
            ->withTimestamps();
    }

    /**
     * Get unread tasks for the department
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
     * Mark a task as viewed by this department
     */
    public function markTaskAsViewed(Task $task): void
    {
        $task->markAsViewedByDepartment($this);
    }

    /**
     * Update task status for this department
     */
    public function updateTaskStatus(Task $task, string $status): void
    {
        $task->updateStatusForDepartment($this, $status);
        
        // Update status for all users in this department who are assigned to the task
        foreach ($this->users as $user) {
            if ($task->users()->where('user_id', $user->id)->exists()) {
                $task->updateStatusForUser($user, $status);
            }
        }
    }
    
    /**
     * Get all users for a task from this department
     */
    public function getUsersForTask(Task $task)
    {
        return $this->users()->whereHas('tasks', function ($query) use ($task) {
            $query->where('tasks.id', $task->id);
        })->get();
    }

    public function branchs()
    {
        return $this->belongsToMany(Branch::class, DepartmentBranch::class, 'department_id', 'branch_id');
    }

    public function departmentHead()
    {
        return $this->users()->whereHas('role', function($query) {
            $query->where('slug', 'department-head');
        })->first();
    }

    public function deputyDepartmentHead()
    {
        return $this->users()->whereHas('role', function($query) {
            $query->where('slug', 'deputy-department-head');
        })->first();
    }

    public function staff()
    {
        return $this->users()->whereHas('role', function($query) {
            $query->where('slug', 'staff');
        })->get();
    }
}
