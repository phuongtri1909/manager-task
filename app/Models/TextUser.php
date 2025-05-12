<?php

namespace App\Models;

use App\Http\Traits\UseAuth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class TextUser extends Pivot
{
    use HasFactory, UseAuth;

    protected $table = 'text_user';

    protected $fillable = [
        'text_id',
        'user_id',
        'status',
        'direction',
        'is_read',
        'created_by',
        'updated_by',
    ];
}
