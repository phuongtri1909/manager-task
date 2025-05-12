<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SolieuPgd extends Model
{
    protected $table = 'sltdxa';
    protected $fillable = [
        'id',
        'NGAYBC',
        'MAPGD',
        'MAXA',
        'TENXA',
        'MATD',
        'SOTO',
        'SOHO',
        'DUNO',
        'DNOQHAN',
        'TLQH',
        'DNOKHOANH',
        'TLKH',
        'DNBQTO',
        'DNBQHO',
        'DNBQXA',        
    ];

    public function district(): BelongsTo
    {
        return $this->belongsTo(District::class, 'MAPGD', 'code');
        
    }

    // public function ward(): BelongsTo
    // {
    //     return $this->belongsTo(Ward::class, 'MAXA', 'code');
    // }
}


