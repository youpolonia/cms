<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentVersion extends Model
{
    protected $fillable = [
        'content_id',
        'version_number',
        'title',
        'body',
        'is_autosave',
        'version_status',
        'expire_at'
    ];

    protected $casts = [
        'is_autosave' => 'boolean',
        'expire_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function content()
    {
        return $this->belongsTo(Content::class);
    }

    public function markSuperseded()
    {
        $this->update(['version_status' => 'superseded']);
    }

    public function markExpired()
    {
        $this->update([
            'version_status' => 'expired',
            'expire_at' => null
        ]);
    }

    public function scopeActive($query)
    {
        return $query->where('version_status', 'active');
    }

    public function scopeSuperseded($query)
    {
        return $query->where('version_status', 'superseded');
    }

    public function scopeExpired($query)
    {
        return $query->where('version_status', 'expired');
    }
}