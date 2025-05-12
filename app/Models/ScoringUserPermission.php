<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScoringUserPermission extends Model
{
    use HasFactory;
    protected $table = 'scoring_user_permission';
    protected $fillable = [
        'scoring_id',
        'user_id',
        'read',
        'create',
        'update',
        'delete',
    ];
}
