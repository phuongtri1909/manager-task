<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Codegdx extends Model
{
    protected $table = 'code_cd_gdx';
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
