<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduledContent extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_id',
        'publish_at',
        'depublish_at',
        'status',
        'scheduled_by',
        'notes'
    ];

    protected $casts = [
        'publish_at' => 'datetime',
        'depublish_at' => 'datetime',
    ];

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    public function scheduledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'scheduled_by');
    }
}