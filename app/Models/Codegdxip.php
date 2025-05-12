<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Codegdxip extends Model
{
    protected $table = 'code_cdip_gdx';
    protected $fillable = [
        'scoring_id',
        'criteria',
        'nd_criteria',
        'point_ladder',
        'point',
        'note',
        'file'
    ];
}
