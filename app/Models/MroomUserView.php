<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MroomUserView extends Model
{
    protected $table = 'mroom_user_views';
    protected $fillable = [
        'mroom_id',
        'user_id',
    ];

    public function mroom()
    {
        return $this->belongsTo(Mroom::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
