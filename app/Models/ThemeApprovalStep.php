<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

class ThemeApprovalStep extends Model
{
    use HasFactory;

    protected $fillable = [
        'workflow_id',
        'name',
        'description',
        'order',
        'approver_role',
        'is_required',
        'required_approvals',
        'approval_logic',
        'rejection_logic',
        'timeout_days',
        'requirements'
    ];

    protected $casts = [
        'requirements' => 'array'
    ];

    public static function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'required|integer|min:1',
            'approver_role' => 'required|string|exists:roles,name',
            'is_required' => 'boolean',
            'required_approvals' => 'required|integer|min:1',
            'approval_logic' => 'nullable|string|in:any,all',
            'rejection_logic' => 'nullable|string|in:any,all',
            'timeout_days' => 'nullable|integer|min:1',
            'requirements' => 'nullable|array',
            'requirements.*' => 'string|max:255',
            'custom_requirements' => 'nullable|array',
            'custom_requirements.*' => 'string|max:500'
        ];
    }

    public function setRequirementsAttribute($value)
    {
        $this->attributes['requirements'] = json_encode($value);
    }

    public function getRequirementsAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }

    public function workflow()
    {
        return $this->belongsTo(ThemeApprovalWorkflow::class);
    }

    public function approvals()
    {
        return $this->hasMany(ThemeVersionApproval::class, 'step_id');
    }

    public function isComplete(): bool
    {
        return $this->approvals()
            ->where('status', 'approved')
            ->count() >= $this->required_approvals;
    }

    public function isPending(): bool
    {
        return $this->approvals()
            ->where('status', 'pending')
            ->exists();
    }

    public function isRejected(): bool
    {
        return $this->approvals()
            ->where('status', 'rejected')
            ->exists();
    }

    public function isTimedOut(): bool
    {
        if (!$this->timeout_days) {
            return false;
        }

        return $this->created_at->addDays($this->timeout_days)->isPast();
    }
}
