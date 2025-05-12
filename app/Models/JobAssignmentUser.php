<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class JobAssignmentUser extends Pivot
{
    use HasFactory;

    protected $table = 'job_assignment_user';

    protected $fillable = [
        'job_assignment_id',
        'user_id',
        'position',
    ];
}
