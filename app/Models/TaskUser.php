<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskUser extends Model
{
    use HasFactory;

    protected $table = 'task_user';

    protected $fillable = [
        'task_id',
        'user_id', //người nhận task và thực hiện task
        'status',
        'viewed_at',
        'completion_date',
        'approved_rejected',
        'approved_rejected_reason',
        'approved_by',
        'approved_at',
        'assigned_by',
        'assigned_at',
        'completion_attempt'
    ];

    /**
     * Task status constants
     */
    const STATUS_SENDING = 'sending';
    const STATUS_VIEWED = 'viewed';
    const STATUS_IN_PROGRESS = 'in_progress';
    const STATUS_COMPLETED = 'completed';
    const STATUS_APPROVAL_REJECTED = 'approval_rejected';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';



    protected $casts = [
        'viewed_at' => 'datetime',
        'completion_date' => 'datetime',
        'approved_at' => 'datetime',
        'assigned_at' => 'datetime',
        'completion_attempt' => 'integer',
    ];

    public function taskUserAttachments()
    {
        return $this->hasMany(TaskUserAttachment::class);
    }

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function assignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }
    public function getStatusTextAttribute(): string
    {
        return match ($this->status) {
            self::STATUS_SENDING => 'Đang gửi',
            self::STATUS_VIEWED => 'Đã xem',
            self::STATUS_IN_PROGRESS => 'Đang thực hiện',
            self::STATUS_COMPLETED => 'Đã hoàn thành',
            self::STATUS_APPROVAL_REJECTED => 'Từ chối kết quả',
            self::STATUS_APPROVED => 'Đã phê duyệt',
            self::STATUS_REJECTED => 'Đã từ chối',
            default => 'Không xác định'
        };
    }

    public function deadlinePassed(): bool
    {
        return $this->completion_date && $this->completion_date < now();
    }
}
