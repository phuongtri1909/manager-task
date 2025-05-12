<?php

namespace App\Models;

use App\Http\Traits\UseActiveScope;
use App\Http\Traits\UseAuth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ward extends Model
{
    use HasFactory, SoftDeletes, UseActiveScope, UseAuth;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'district_id',
        'code',
        'name',
        'active',
    ];

    public function district(): BelongsTo
    {        
        // return $this->belongsTo(District::class, 'district_id', 'code');
        return $this->belongsTo(District::class);
    }

    public function managers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_ward');
    }
}
