<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Role extends Model
{
    use HasFactory, SoftDeletes;

    // Role scope constants
    const SCOPE_GLOBAL = 'global';
    const SCOPE_DEPARTMENT = 'department';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'level',
        'scope', // Either 'global' or 'department'
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
    
    /**
     * Check if the role is department-specific
     *
     * @return bool
     */
    public function isDepartmentSpecific(): bool
    {
        return $this->scope === self::SCOPE_DEPARTMENT;
    }
    
    /**
     * Check if the role is global (system-wide)
     *
     * @return bool
     */
    public function isGlobal(): bool
    {
        return $this->scope === self::SCOPE_GLOBAL;
    }
} 