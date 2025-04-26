<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ErrorResolutionWorkflow extends Model
{
    protected $fillable = [
        'name',
        'description', 
        'is_active',
        'error_category_id'
    ];

    public function steps(): HasMany
    {
        return $this->hasMany(ErrorResolutionStep::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getStepsInOrder()
    {
        return $this->steps()->orderBy('order')->get();
    }
}