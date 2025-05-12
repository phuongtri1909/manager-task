<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cdgdx01 extends Model
{
    use HasFactory;
    protected $table = 'cdgdx01s';
    protected $fillable = [
        'scoring_id',
        'criteria',
        'point_ladder',
        'point',
        'parent_id',
        'note',
        'tt_hienthi',
        'ma'
    ];
}
