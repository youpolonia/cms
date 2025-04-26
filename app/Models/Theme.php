<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Theme extends Model
{
    protected $fillable = [
        'name',
        'description',
        'version_history',
        'is_active'
    ];

    protected $casts = [
        'version_history' => 'array',
        'is_active' => 'boolean'
    ];

    public function versions(): HasMany
    {
        return $this->hasMany(ThemeVersion::class);
    }

    public function activeVersion(): HasOne
    {
        return $this->hasOne(ThemeVersion::class)
            ->where('is_active', true);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(ThemeBranch::class);
    }

    public function scopeWithActiveVersion($query)
    {
        return $query->with('activeVersion');
    }

    public function scopeWithVersions($query)
    {
        return $query->with('versions');
    }
}
