<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class ScoringIP extends Model
{
    protected $table = 'scoringsip';

    protected $fillable = [
        'date_check',
        'level',
        'created_at',
        'updated_at',
        'ward_id',
        'user_boss',
        'user_2',
        'user_1',
        'user_check',
        'status',
        'created_by',
        'updated_by'
    ];




}
