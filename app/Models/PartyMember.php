<?php

namespace App\Models;

use App\Http\Traits\UseActiveScope;
use App\Http\Traits\UseAuth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PartyMember extends Model
{
    use HasFactory, SoftDeletes, UseAuth, UseActiveScope;

    protected $fillable = [
        'user_id',
        'nation_id',
        'religion_id',
        'party_id',
        'name',
        'avatar',
        'gender',
        'date_of_birth',
        'alias',
        'residence',
        'shelter',
        'education_level',
        'vocational_education',
        'postgraduate',
        'foreign_language',
        'information_technology',
        'joining_date',
        'joining_place',
        'recognition_date',
        'recognition_place_1',
        'recognition_place_2',
        'academic_rank',
        'political_theory',
        'party_position',
        'union_position',
        'status',
        'official_code',
        'reserve_code',
        'position_salary_coefficient',
        'responsibility_salary_coefficient',
        'toxic_salary_coefficient',
        'regional_allowance',
        'regional_minimum_wage',
        'free_party_fee',
        'active',
        'created_by',
        'updated_by',
        'sx',
    ];

    public function nation(): BelongsTo
    {
        return $this->belongsTo(Nation::class);
    }

    public function religion(): BelongsTo
    {
        return $this->belongsTo(Religion::class);
    }

    public function party(): BelongsTo
    {
        return $this->belongsTo(Party::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getDateOfBirthAttribute(string $value): ?Carbon
    {
        return $value ? Carbon::create($value) : null;
    }

    public function getJoiningDateAttribute(string $value): ?Carbon
    {
        return $value ? Carbon::create($value) : null;
    }

    public function getUnionJoiningDateAttribute(string $value): ?Carbon
    {
        return $value ? Carbon::create($value) : null;
    }

    public function getRecognitionDateAttribute(string $value): ?Carbon
    {
        return $value ? Carbon::create($value) : null;
    }
}
