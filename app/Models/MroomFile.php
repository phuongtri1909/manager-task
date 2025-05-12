<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MroomFile extends Model
{
    use HasFactory;
    protected $table = 'mroom_files';
    protected $fillable = [
        'name',
        'type',
        'url',
        'mroom_id',
        'description'
    ];

    public function mroom()
    {
        return $this->belongsTo(Mroom::class);
    }
}
