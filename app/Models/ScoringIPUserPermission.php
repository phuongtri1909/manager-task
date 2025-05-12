<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScoringIPUserPermission extends Model
{
    use HasFactory;
    protected $table = 'scoringip_user_permission';
    protected $fillable = [
        'scoring_id',
        'user_id',
        'read',
        'create',
        'update',
        'delete',
    ];
}
