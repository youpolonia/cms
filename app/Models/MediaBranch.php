<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaBranch extends Model
{
    use HasFactory;

    protected $fillable = [
        'media_id',
        'name',
        'description',
        'base_version_id',
        'is_default',
        'created_by'
    ];

    public function media()
    {
        return $this->belongsTo(Media::class);
    }

    public function baseVersion()
    {
        return $this->belongsTo(MediaVersion::class, 'base_version_id');
    }

    public function versions()
    {
        return $this->hasMany(MediaVersion::class, 'branch_name', 'name');
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function getLatestVersionAttribute()
    {
        return $this->versions()->orderByDesc('version_number')->first();
    }

    public function mergeFromBranch(MediaBranch $sourceBranch): bool
    {
        $latestSourceVersion = $sourceBranch->latestVersion;
        if (!$latestSourceVersion) {
            return false;
        }

        return $latestSourceVersion->mergeIntoBranch($this->name);
    }
}
