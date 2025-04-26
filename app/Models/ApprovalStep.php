<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalStep extends Model
{
    protected $fillable = [
        'workflow_id',
        'name',
        'description',
        'order',
        'approver_type',
        'approver_ids',
        'is_required'
    ];

    protected $casts = [
        'approver_ids' => 'array',
        'is_required' => 'boolean'
    ];

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(ApprovalWorkflow::class);
    }

    public function getApproversAttribute()
    {
        return match($this->approver_type) {
            'user' => User::whereIn('id', $this->approver_ids)->get(),
            'role' => Role::whereIn('id', $this->approver_ids)->get(),
            'team' => Team::whereIn('id', $this->approver_ids)->get(),
            default => collect()
        };
    }
}
