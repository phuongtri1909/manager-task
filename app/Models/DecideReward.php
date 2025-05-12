<?php

namespace App\Models;

use App\Http\Traits\UseActiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DecideReward extends Model
{
    use HasFactory, SoftDeletes, UseActiveScope;
    protected $table = 'rewards';
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'dicision_number',
        'date',
        'unit_id',
        'year',
        'signer',
        'signer_position',
        'type',
        'reward_form',
        'content',
        //'is_personal',
    ];

    public function unit(): BelongsTo
    {
       
        return $this->belongsTo(Unitcd::class);
    }

    public function unitcd(): BelongsTo
    {
       
        return $this->belongsTo(Unitcd::class);
    }

}
