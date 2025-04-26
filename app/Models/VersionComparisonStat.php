<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VersionComparisonStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'version_a_id',
        'version_b_id',
        'content_id',
        'user_id',
        'similarity_percentage',
        'lines_added',
        'lines_removed',
        'lines_unchanged',
        'words_added',
        'words_removed',
        'words_unchanged',
        'frequent_changes',
        'change_distribution'
    ];

    protected $casts = [
        'frequent_changes' => 'array',
        'change_distribution' => 'array',
        'compared_at' => 'datetime'
    ];

    public function versionA()
    {
        return $this->belongsTo(ContentVersion::class, 'version_a_id');
    }

    public function versionB()
    {
        return $this->belongsTo(ContentVersion::class, 'version_b_id');
    }

    public function content()
    {
        return $this->belongsTo(Content::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
