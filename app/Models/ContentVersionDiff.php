<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentVersionDiff extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_id',
        'from_version_id',
        'to_version_id',
        'diff_data',
        'summary'
    ];

    protected $casts = [
        'diff_data' => 'array'
    ];

    public function content()
    {
        return $this->belongsTo(Content::class);
    }

    public function fromVersion()
    {
        return $this->belongsTo(ContentVersion::class, 'from_version_id');
    }

    public function toVersion()
    {
        return $this->belongsTo(ContentVersion::class, 'to_version_id');
    }

    public function getChangesAttribute()
    {
        return $this->diff_data['changes'] ?? [];
    }

    public function getAddedCountAttribute()
    {
        return count(array_filter($this->changes, fn($line) => str_starts_with($line, '+')));
    }

    public function getRemovedCountAttribute()
    {
        return count(array_filter($this->changes, fn($line) => str_starts_with($line, '-')));
    }
}
