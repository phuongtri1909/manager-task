<?php

namespace App\Models;

use App\Http\Traits\UseAuth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TaskUserAttachment extends Model
{
    use HasFactory, SoftDeletes, UseAuth;

    protected $fillable = [
        'task_user_id',
        'filename',
        'original_filename',
        'file_path',
        'file_type',
        'file_size',
        'uploaded_by',
        'completion_attempt',
        'description',
        'is_active'
    ];

    /**
     * Get the task that owns the attachment.
     */
    public function taskUser(): BelongsTo
    {
        return $this->belongsTo(TaskUser::class, 'task_user_id');
    }

    /**
     * Get the user who uploaded the attachment.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Check if file is a document (.doc, .docx, .pdf, etc.)
     */
    public function isDocument(): bool
    {
        return in_array($this->file_type, ['doc', 'docx', 'pdf', 'txt', 'rtf']);
    }

    /**
     * Check if file is a spreadsheet (.xls, .xlsx, etc.)
     */
    public function isSpreadsheet(): bool
    {
        return in_array($this->file_type, ['xls', 'xlsx', 'csv']);
    }

    /**
     * Check if file is a video (.mp4, etc.)
     */
    public function isVideo(): bool
    {
        return in_array($this->file_type, ['mp4', 'avi', 'mov', 'wmv']);
    }

    /**
     * Check if file is an image
     */
    public function isImage(): bool
    {
        return in_array($this->file_type, ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'tiff', 'ico']);
    }
} 