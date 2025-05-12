<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScoringFile extends Model
{
    use HasFactory;
    protected $table = 'scoring_files';
    protected $fillable = [
        'name',
        'type',
        'url',
        'scoring_id',
        'description'
    ];

    public function scoring()
    {
        return $this->belongsTo(Scoring::class);
    }
}
