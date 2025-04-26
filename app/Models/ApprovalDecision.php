<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalDecision extends Model
{
    protected $fillable = [
        'content_id',
        'step_id',
        'user_id',
        'decision',
        'comments',
        'changes_requested'
    ];

    protected $casts = [
        'changes_requested' => 'array',
        'decision_at' => 'datetime'
    ];

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(ApprovalStep::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isApproved(): bool
    {
        return $this->decision === 'approved';
    }

    public function isRejected(): bool
    {
        return $this->decision === 'rejected';
    }

    public function hasChangesRequested(): bool
    {
        return $this->decision === 'changes_requested';
    }
}
