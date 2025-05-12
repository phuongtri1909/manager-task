<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepartmentBranch extends Model
{
    protected $table = 'department_branch';
    protected $fillable = [
        'id',
        'department_id',
        'branch_id',
        'created_at',
        'updated_at',
    ];
}
