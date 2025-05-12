<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Khtd extends Model
{
    protected $table = 'khtd';
    protected $fillable = [
        'id',
        'ngaybc',
        'donvi',
        'name',
        'path',
    ];
}
