<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnalyticsExport extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'content_id',
        'file_path',
        'status',
        'expires_at',
        'format',
        'type',
        'progress',
        'anonymize',
        'anonymization_options'
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'anonymization_options' => 'array',
        'progress' => 'integer'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getAnonymizationOptions(): array
    {
        return $this->anonymization_options ?? [
            'remove_pii' => true,
            'hash_identifiers' => true,
            'remove_ip' => true,
            'remove_user_agent' => true
        ];
    }

    /**
     * The tags that belong to the export.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(
            related: ExportTag::class,
            table: 'analytics_export_tags',
            foreignPivotKey: 'export_id',
            relatedPivotKey: 'tag_id'
        );
    }

    /**
     * Sync tags for this export
     */
    public function syncTags(array $tags): void
    {
        $tagIds = ExportTag::whereIn('name', $tags)->pluck('id');
        $this->tags()->sync($tagIds);
    }

    /**
     * Scope a query to only include exports with given tags.
     */
    public function scopeWithTags($query, array $tags)
    {
        return $query->whereHas('tags', function($q) use ($tags) {
            $q->whereIn('name', $tags);
        });
    }
}
