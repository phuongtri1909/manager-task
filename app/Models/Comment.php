<?php

namespace App\Models;

use App\Http\Traits\UseAuth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Comment extends Model
{
    use HasFactory, UseAuth;

    protected $fillable = [
        'commentable_id',
        'commentable_type',
        'content',
    ];

    public function commentable(): MorphTo
    {
        return $this->morphTo();
    }
}
