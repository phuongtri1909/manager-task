<?php

namespace App\Http\Traits;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait UseAuth
{
    /**
     * Get the creator that owns the UseAuth
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the updater that owns the UseAuth
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function (Model $model) {
            if (!$model->created_by) {
                $model->created_by = auth()->id();
            }
        });
        static::updating(function (Model $model) {
            $model->updated_by = auth()->id();
        });
    }

    public function setAttributes($attributes)
    {
        $authUser = auth()->user();
        if ($authUser && is_array($attributes) && !array_key_exists('updated_by', $attributes)) {
            $attributes['updated_by'] = $authUser->id;
        }
        if ($authUser && is_array($attributes) && !array_key_exists('created_by', $attributes)) {
            $attributes['created_by'] = $authUser->id;
        }
        return $attributes;
    }
    
    public function fillWithAuth($attributes)
    {
        return $this->fill($this->setAttributes($attributes));
    }
}
