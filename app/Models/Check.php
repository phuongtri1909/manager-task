<?php

namespace App\Models;

use App\Http\Traits\UseActiveScope;
use App\Http\Traits\UseAuth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Check extends Model
{
    use HasFactory, SoftDeletes, UseActiveScope, UseAuth;

    protected $fillable = [
        'district_id',
        'ward_id',
        'town_id',
        'household_id',
        'unit_id',
        'time',
        'debt',
        'balance',
        'number_of_groups',
        'number_of_borrowers',
        'unit_check_id',
        'description',
        'active',
        'created_by',
        'updated_by',
    ];

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class);
    }

    public function ward(): BelongsTo
    {
        return $this->belongsTo(Ward::class);
    }

    public function town(): BelongsTo
    {
        return $this->belongsTo(Town::class);
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }
    public function household(): BelongsTo
    {
        return $this->belongsTo(Household::class);
    }

    public function getTimeAttribute(string $value): Carbon
    {
        return Carbon::create($value);
    }
}
