<?php

namespace App\Models;

use App\Http\Traits\UseAuth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Task extends Model
{
    use HasFactory, SoftDeletes, UseAuth;

    protected $fillable = [
        'title',
        'description',
        'deadline',
        'created_by',
        'updated_by',
        'for_departments',
        'include_department_heads',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'include_department_heads' => 'boolean',
    ];

    /**
     * Task status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    /**
     * Get the task_departments relationship
     */
    public function taskDepartments(): HasMany
    {
        return $this->hasMany(TaskDepartment::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'task_departments')
            ->withPivot('include_department_heads')
            ->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_user')
            ->withPivot('status', 'viewed_at', 'completion_date', 
                         'approved_rejected', 'approved_rejected_reason',
                         'approved_by', 'approved_at', 'assigned_by', 'assigned_at')
            ->withTimestamps();
    }

    public function extensions(): HasMany
    {
        return $this->hasMany(TaskExtension::class);
    }

    /**
     * Get all attachments for the task
     */
    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class);
    }

    /**
     * Check if a user has viewed this task
     */
    public function isViewedByUser(User $user): bool
    {
        $taskUser = TaskUser::where('task_id', $this->id)
            ->where('user_id', $user->id)
            ->first();
            
        return $taskUser && $taskUser->viewed_at !== null;
    }
    
    /**
     * Mark task as viewed by the user
     */
    public function markAsViewedByUser(User $user): void
    {
        $taskUser = TaskUser::where('task_id', $this->id)
            ->where('user_id', $user->id)
            ->first();
            
        if ($taskUser && $taskUser->viewed_at === null) {
            $taskUser->update(['viewed_at' => now()]);
        }
    }
    
    /**
     * Update task status for user
     */
    public function updateStatusForUser(User $user, string $status): void
    {
        $taskUser = TaskUser::where('task_id', $this->id)
            ->where('user_id', $user->id)
            ->first();
            
        if ($taskUser) {
            $taskUser->update(['status' => $status]);
            
            // If status is completed, set completion date
            if ($status === self::STATUS_COMPLETED) {
                $taskUser->update(['completion_date' => now()]);
            }
        }
    }
    
    /**
     * Assign task to a user
     */
    public function assignToUser(User $user): void
    {
        if (!$this->users()->where('user_id', $user->id)->exists()) {
            $this->users()->attach($user->id, [
                'status' => self::STATUS_PENDING,
            ]);
        }
    }
    
    /**
     * Assign task to multiple users
     */
    public function assignToUsers(array $userIds): void
    {
        $data = [];
        foreach ($userIds as $userId) {
            if (!$this->users()->where('user_id', $userId)->exists()) {
                $data[$userId] = ['status' => self::STATUS_PENDING];
            }
        }
        
        if (!empty($data)) {
            $this->users()->attach($data);
        }
    }
    
    /**
     * Assign task to multiple departments
     */
    public function assignToDepartments(array $departmentIds): void
    {
        foreach ($departmentIds as $departmentId) {
            $department = Department::find($departmentId);
            if ($department) {
                $this->assignToDepartment($department);
            }
        }
    }

    /**
     * Assign task to a department
     */
    public function assignToDepartment(Department $department): void
    {
        if (!$this->departments()->where('department_id', $department->id)->exists()) {
            $this->departments()->attach($department->id, [
                'viewed_at' => null,
            ]);
        }
    }
    
    /**
     * Add a comment to the task
     */
    public function addComment(User $user, string $comment, ?string $statusUpdate = null): TaskComment
    {
        return $this->comments()->create([
            'user_id' => $user->id,
            'comment' => $comment,
            'status_update' => $statusUpdate,
        ]);
    }
    
   
    public function addCommentWithAttachments(User $user, string $comment, array $attachments, ?string $statusUpdate = null): TaskComment
    {
        $taskComment = $this->addComment($user, $comment, $statusUpdate);
        foreach ($attachments as $attachment) {
            if (!isset($attachment['uploaded_by'])) {
                $attachment['uploaded_by'] = $user->id;
            }
            
            $taskComment->addAttachment($attachment);
        }
        
        return $taskComment;
    }
    
    public function updateStatusForUserWithComment(User $user, string $status, string $comment, array $attachments = []): void
    {
        $this->updateStatusForUser($user, $status);
        $this->addCommentWithAttachments($user, $comment, $attachments, $status);
    }
}