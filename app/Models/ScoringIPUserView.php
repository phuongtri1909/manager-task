<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScoringIPUserView extends Model
{
    protected $table = 'scoringip_user_views';
    protected $fillable = [
        'scoring_id',
        'user_id',
    ];

    public function scoring()
    {
        return $this->belongsTo(Scoring::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
