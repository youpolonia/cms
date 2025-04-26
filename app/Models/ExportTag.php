<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ExportTag extends Model
{
    protected $fillable = ['name', 'color'];

    /**
     * The exports that belong to the tag.
     */
    public function exports(): BelongsToMany
    {
        return $this->belongsToMany(
            related: AnalyticsExport::class,
            table: 'analytics_export_tags',
            foreignPivotKey: 'tag_id',
            relatedPivotKey: 'export_id'
        );
    }

    /**
     * Scope a query to only include tags matching search term.
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where('name', 'like', "%{$search}%");
    }
}