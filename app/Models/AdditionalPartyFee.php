<?php

namespace App\Models;

use App\Http\Traits\UseActiveScope;
use App\Http\Traits\UseAuth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdditionalPartyFee extends Model
{
    use HasFactory, SoftDeletes, UseAuth, UseActiveScope;

    protected $fillable = [
        'party_member_id',
        'time',
        'old_salary',
        'new_salary',
        'deviation',
        'count_months',
        'amount',
        'active',
        'created_by',
        'updated_by',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(PartyMember::class, 'party_member_id');
    }

    public function getTimeAttribute(string $value): Carbon
    {
        return Carbon::create($value);
    }
}
