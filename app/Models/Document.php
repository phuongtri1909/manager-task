<?php

namespace App\Models;

use App\Http\Traits\UseAuth;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Document extends Model
{
    use HasFactory, SoftDeletes, UseAuth;

    protected $fillable = [
        'documentable_id',
        'documentable_type',
        'name',
        'alias',
        'path',
        'size',
        'type',
        'description',
        'created_by',
        'updated_by',
    ];

    public function documentable()
    {
        return $this->morphTo();
    }
}
