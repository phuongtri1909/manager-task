<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskDepartment extends Model
{
    use HasFactory;

    protected $table = 'task_departments';
    protected $fillable = [
        'task_id',
        'department_id',
        'include_department_heads',
    ];

    protected $casts = [
        'include_department_heads' => 'boolean',
    ];

    public function task()
    {
        return $this->belongsTo(Task::class);
    }
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    public function taskUsers()
    {
        return $this->hasMany(TaskUser::class);
    }
}
