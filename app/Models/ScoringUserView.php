<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScoringUserView extends Model
{
    protected $table = 'scoring_user_views';
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
