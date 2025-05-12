<?php

namespace App\Models;

use App\Http\Traits\UseActiveScope;
use App\Http\Traits\UseAuth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class OtherFee extends Model
{
    use HasFactory, SoftDeletes, UseAuth, UseActiveScope;

    protected $fillable = [
        'handler_id',
        'amount',
        'time',
        'type',
        'direction',
        'description',
        'active',
        'created_by',
        'updated_by',
    ];

    public function handler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'handler_id');
    }

    public function documents(): MorphMany
    {
        return $this->morphMany(Document::class, 'documentable');
    }

    public function getTimeAttribute(string $value): Carbon
    {
        return Carbon::create($value);
    }
}
