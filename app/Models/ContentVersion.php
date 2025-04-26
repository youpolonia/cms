<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentVersion extends Model
{
    protected $fillable = [
        'title',
        'content',
        'branch_id',
        'is_autosave',
        'restored_from_id'
    ];

    protected $casts = [
        'is_autosave' => 'boolean'
    ];

    public function contentItem(): BelongsTo
    {
        return $this->belongsTo(Content::class, 'content_id');
    }

    public function diffs(): HasMany
    {
        return $this->hasMany(ContentVersionDiff::class);
    }

    public function restoredFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'restored_from_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(ContentVersion::class, 'restored_from_id');
    }
}
