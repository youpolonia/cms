<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ThemeBranch extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'status',
        'theme_id',
        'is_protected',
        'is_default',
        'merge_strategy',
        'base_version_id'
    ];

    protected $casts = [
        'is_protected' => 'boolean',
        'is_default' => 'boolean'
    ];

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ThemeVersion::class);
    }

    public function latestVersion(): HasMany
    {
        return $this->hasMany(ThemeVersion::class)
            ->latest()
            ->limit(1);
    }

    public function baseVersion(): BelongsTo
    {
        return $this->belongsTo(ThemeVersion::class, 'base_version_id');
    }

    public function isProtected(): bool
    {
        return $this->is_protected;
    }

    public function isDefault(): bool
    {
        return $this->is_default;
    }

    public function canMerge(): bool
    {
        return !$this->is_protected;
    }

    public function createFromVersion(ThemeVersion $version, string $name, string $description = ''): self
    {
        return $this->create([
            'name' => $name,
            'description' => $description,
            'theme_id' => $version->theme_id,
            'base_version_id' => $version->id,
            'status' => 'active',
            'is_protected' => false,
            'is_default' => false
        ]);
    }

    public function mergeInto(ThemeBranch $targetBranch, string $message = ''): ThemeVersion
    {
        if (!$this->canMerge() || !$targetBranch->canMerge()) {
            throw new \RuntimeException('One or both branches are protected and cannot be merged');
        }

        $latestVersion = $this->latestVersion()->first();
        
        return $targetBranch->versions()->create([
            'theme_id' => $targetBranch->theme_id,
            'branch_id' => $targetBranch->id,
            'parent_version_id' => $latestVersion->id,
            'version' => $this->generateNextVersion($targetBranch),
            'description' => $message,
            'status' => 'draft',
            'file_changes' => $latestVersion->file_changes,
            'diff_data' => $latestVersion->diff_data
        ]);
    }

    protected function generateNextVersion(ThemeBranch $branch): string
    {
        $latest = $branch->latestVersion()->first();
        if (!$latest) {
            return '1.0.0';
        }

        $parts = explode('.', $latest->getSemanticVersion());
        $parts[2] = (int)$parts[2] + 1; // Increment patch version
        
        return implode('.', $parts);
    }
}
