<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ThemeVersion extends Model
{
    use HasFactory;
    protected $fillable = [
        'theme_id',
        'version',
        'manifest',
        'changelog',
        'is_active',
        'parent_version_id',
        'file_changes',
        'diff_data',
        'is_rollback',
        'branch_name',
        'tags'
    ];

    protected $casts = [
        'manifest' => 'array',
        'file_changes' => 'array',
        'diff_data' => 'array',
        'tags' => 'array'
    ];

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }

    public function parentVersion(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_version_id');
    }

    public function branches(): HasMany
    {
        return $this->hasMany(self::class, 'parent_version_id');
    }

    public function isMainBranch(): bool
    {
        return $this->branch_name === null;
    }

    public function getTagListAttribute(): array
    {
        return $this->tags ?? [];
    }
}
