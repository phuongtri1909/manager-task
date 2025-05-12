<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    protected $table = 'branch';
    protected $fillable = [
        'id',
        'code',
        'name',
        'created_at',
        'updated_at',
    ];
}
