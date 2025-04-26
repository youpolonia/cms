<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VersionComparison extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'content_id',
        'version_a_id',
        'version_b_id',
        'lines_changed',
        'words_changed',
        'similarity_score',
        'significant_changes',
        'change_rate',
        'time_between',
        'compared_at'
    ];

    protected $casts = [
        'compared_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function content()
    {
        return $this->belongsTo(Content::class);
    }

    public function versionA()
    {
        return $this->belongsTo(ContentVersion::class, 'version_a_id');
    }

    public function versionB()
    {
        return $this->belongsTo(ContentVersion::class, 'version_b_id');
    }

    public function scopeRecent($query, $days = 7)
    {
        return $query->where('compared_at', '>=', now()->subDays($days));
    }

    public function scopeForContent($query, $contentId)
    {
        return $query->where('content_id', $contentId);
    }

    public function scopeWithSignificantChanges($query)
    {
        return $query->where('significant_changes', '>', 0);
    }
}