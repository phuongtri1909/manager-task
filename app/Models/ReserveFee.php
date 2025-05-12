<?php

namespace App\Models;

use App\Http\Traits\UseActiveScope;
use App\Http\Traits\UseAuth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ReserveFee extends Model
{
    use HasFactory, SoftDeletes, UseAuth, UseActiveScope;

    protected $fillable = [
        'time',
        'amount',
        'description',
        'active',
        'created_by',
        'updated_by',
    ];

    public function getTimeAttribute(string $value): Carbon
    {
        return Carbon::create($value);
    }
}
