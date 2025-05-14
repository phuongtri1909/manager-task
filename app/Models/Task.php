<?php

namespace App\Models;

use App\Http\Traits\UseAuth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes, UseAuth;

    protected $fillable = [
        'title',
        'description',
        'deadline',
        'status',
        'created_by',
        'updated_by',
        'for_departments',
        'include_department_heads',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'for_departments' => 'boolean',
        'include_department_heads' => 'boolean',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'department_task')
            ->withPivot('status', 'viewed_at')
            ->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_user')
            ->withPivot('status', 'viewed_at', 'completion_date', 'approved_by', 'approved_at')
            ->withTimestamps();
    }

    public function extensions(): HasMany
    {
        return $this->hasMany(TaskExtension::class);
    }
} 