<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Mroom extends Model
{
    protected $table = 'mrooms';

    protected $fillable = [
        'time',
        'name',
        'place',
        'user_boss',
        'user_1',
        'created_at',
        'updated_at',        
        'created_by',
        'updated_by'
    ];




}
