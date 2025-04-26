<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AutopilotTask extends Model
{
    protected $table = 'autopilot_tasks';
    protected $guarded = [];
    protected $casts = [
        'payload' => 'array',
        'available_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime'
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeAvailable($query)
    {
        return $query->where('available_at', '<=', now());
    }

    public function scopeFailed($query)
    {
        return $query->where('status', self::STATUS_FAILED);
    }
}