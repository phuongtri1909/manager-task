<?php

namespace App\Models;

use App\Http\Traits\UseActiveScope;
use App\Http\Traits\UseAuth;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, UseAuth, UseActiveScope;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'code',
        'name',
        'email',
        'password',
        'department_id',
        'position_id',
        'unit_id',
        'can_assign_job',
        'code_for_job_assignment',
        'co_tong_hop',
        'ma_chuc_vu',
        'ma_don_vi_cong_tac',
        'nguoi_nhap',
        'id_can_bo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function wards(): BelongsToMany
    {
        return $this->belongsToMany(Ward::class, 'user_ward');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_user');
    }

    public function adminlte_profile_url()
    {
        return 'profile';
    }
    public function permissionScoring(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_user');
    }
    public function permissionScoringIP(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_user');
    }
    public function permissionMroom(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class, 'permission_user');
    }
}
