<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ThemeVersionRollback extends Model
{
    use HasFactory;

    protected $fillable = [
        'version_id',
        'rollback_to_version_id',
        'status',
        'notes',
        'error_message',
        'completed_at',
        'user_id',
        'started_at',
        'file_count',
        'file_size_kb',
        'system_metrics',
        'reason',
        'performance_impact',
        'stability_impact',
        'notification_preferences',
        'user_behavior_metrics'
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'started_at' => 'datetime',
        'system_metrics' => 'json',
        'performance_impact' => 'json',
        'stability_impact' => 'json',
        'notification_preferences' => 'json',
        'user_behavior_metrics' => 'json'
    ];

    public function version(): BelongsTo
    {
        return $this->belongsTo(ThemeVersion::class, 'version_id');
    }

    public function rollbackToVersion(): BelongsTo
    {
        return $this->belongsTo(ThemeVersion::class, 'rollback_to_version_id');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeProcessing($query)
    {
        return $query->where('status', 'processing');
    }
}
