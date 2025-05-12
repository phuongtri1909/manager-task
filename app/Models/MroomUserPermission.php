<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MroomUserPermission extends Model
{
    use HasFactory;
    protected $table = 'mroom_user_permission';
    protected $fillable = [
        'mroom_id',
        'user_id',
        'read',
        'create',
        'update',
        'delete',
    ];
}
