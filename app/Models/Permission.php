<?php

namespace App\Models;

use App\Http\Traits\UseActiveScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Permission extends Model
{
    use HasFactory, SoftDeletes, UseActiveScope;

    protected $fillable = [
        'feature_slug',
        'permission_slug',
        'name',
        'active',
    ];

    public function getSlugAttribute(): string
    {
        return $this->feature_slug . '_' . $this->permission_slug;
    }

    public function departments(): BelongsToMany
    {
        return $this->belongsToMany(Department::class, 'department_permission');
    }
}
