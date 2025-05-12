<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ScoringIPFile extends Model
{
    use HasFactory;
    protected $table = 'scoringip_files';
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
