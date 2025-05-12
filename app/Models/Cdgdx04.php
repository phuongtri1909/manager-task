<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cdgdx04 extends Model
{
    use HasFactory;
    protected $table = 'cdgdx04s';
    protected $fillable = [
        'scoring_id',        
        'criteria',
        'nd_criteria',
        'point_ladder',
        'point',
        'parent_id',
        'note',
        'file',
        'file_second',
        'file_third',
    ];

    public function getFileUrlAttribute()
    {
        return (!empty($this->file)) ? asset('storage/image/ScoringIp/' . $this->id . '/' . $this->file) : null;
    }
}
