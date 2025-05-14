<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Content extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'body',
        'status',
        'publish_at',
        'archive_at',
        'expire_at',
        'lifecycle_status'
    ];

    protected $casts = [
        'publish_at' => 'datetime',
        'archive_at' => 'datetime',
        'expire_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    public function versions()
    {
        return $this->hasMany(ContentVersion::class);
    }

    public function schedulePublish($publishAt)
    {
        $this->update([
            'publish_at' => $publishAt,
            'lifecycle_status' => 'scheduled'
        ]);
    }

    public function publish()
    {
        $this->update([
            'publish_at' => null,
            'lifecycle_status' => 'published'
        ]);
    }

    public function archive()
    {
        $this->update([
            'archive_at' => null,
            'lifecycle_status' => 'archived'
        ]);
    }

    public function expire()
    {
        $this->update([
            'expire_at' => null,
            'lifecycle_status' => 'expired'
        ]);
    }

    public function scopeScheduled($query)
    {
        return $query->where('lifecycle_status', 'scheduled');
    }

    public function scopePublished($query)
    {
        return $query->where('lifecycle_status', 'published');
    }

    public function scopeExpired($query)
    {
        return $query->where('lifecycle_status', 'expired');
    }
}