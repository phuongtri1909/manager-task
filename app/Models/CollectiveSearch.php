<?php

namespace App\Models;

use App\Http\Traits\UseActiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CollectiveSearch extends Model
{
    use HasFactory, SoftDeletes, UseActiveScope;
    protected $table = 'groups';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'unit_id',
        'reward_id',
    ];

    public function unit(): BelongsTo
    {      
        return $this->belongsTo(Unitcd::class);
    }
    
    public function reward(): BelongsTo
    {
       // dd($groups);die(); 
        return $this->belongsTo(DecideReward::class);
    }
    
}
