<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dienbao extends Model
{
    protected $table = 'dienbao_ngay';
    protected $fillable = [
        'id',
        'stt',
        'kh',
        'ms',
        'ngay',
        'ctdb',
        'icn1',
        'icn2',
        'icn3',
        'icn4',
        'icn5',
        'icn6',
        'icn7',
        'icn8',
        'icn9',
        'icn10',
        'icn11',
        'icn12',
        'icnn',
    ];
}
