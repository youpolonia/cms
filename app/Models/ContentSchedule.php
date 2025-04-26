<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_id',
        'publish_at', 
        'unpublish_at',
        'status'
    ];

    protected $casts = [
        'publish_at' => 'datetime',
        'unpublish_at' => 'datetime',
    ];

    public function content()
    {
        return $this->belongsTo(Content::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending')
            ->where('publish_at', '<=', now());
    }
}