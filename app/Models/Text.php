<?php

namespace App\Models;

use App\Http\Traits\UseActiveScope;
use App\Http\Traits\UseAuth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Text extends Model
{
    use HasFactory, SoftDeletes, UseAuth, UseActiveScope;

    protected $fillable = [
        'code',
        'agency',
        'time',
        'status',
        'direction',
        'keywords',
        'active',
        'created_by',
        'updated_by',
    ];

    public function document(): MorphOne
    {
        return $this->morphOne(Document::class, 'documentable');
    }

    public function handlers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'text_user',
            'text_id',
            'user_id'
        )
            ->withPivot('status', 'direction', 'is_read', 'created_at', 'updated_at', 'created_by', 'updated_by')
            ->using(TextUser::class);
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function getTimeAttribute(string $value): Carbon
    {
        return Carbon::create($value);
    }
}
