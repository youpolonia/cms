<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModerationComment extends Model
{
    protected $fillable = [
        'moderation_id',
        'user_id',
        'comment'
    ];

    public function moderation(): BelongsTo
    {
        return $this->belongsTo(ModerationQueue::class, 'moderation_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}