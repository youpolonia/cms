<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThemeVersionComparisonStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'theme_version_id',
        'compared_version_id',
        'files_added',
        'files_removed',
        'files_modified',
        'lines_added',
        'lines_removed',
        'quality_score',
        'complexity_change',
        'coverage_change',
        'performance_impact',
        'comparison_data'
    ];

    protected $casts = [
        'comparison_data' => 'array',
        'quality_score' => 'decimal:2',
        'performance_impact' => 'decimal:2',
        'coverage_change' => 'decimal:2'
    ];

    public function themeVersion(): BelongsTo
    {
        return $this->belongsTo(ThemeVersion::class);
    }

    public function comparedVersion(): BelongsTo
    {
        return $this->belongsTo(ThemeVersion::class, 'compared_version_id');
    }

    public function scopeForVersion($query, $versionId)
    {
        return $query->where('theme_version_id', $versionId);
    }

    public function scopeComparedToVersion($query, $versionId)
    {
        return $query->where('compared_version_id', $versionId);
    }

    public function getChangeSummaryAttribute(): array
    {
        return [
            'files' => [
                'added' => $this->files_added,
                'removed' => $this->files_removed,
                'modified' => $this->files_modified
            ],
            'lines' => [
                'added' => $this->lines_added,
                'removed' => $this->lines_removed
            ],
            'quality' => $this->quality_score,
            'performance' => $this->performance_impact
        ];
    }
}
