<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ThemeApprovalWorkflow extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'required_approvers_count',
        'required_roles',
        'sequential_approval',
        'step_timeout_hours',
        'escalation_roles',
        'auto_approve_after_timeout'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sequential_approval' => 'boolean',
        'required_roles' => 'array',
        'escalation_roles' => 'array',
        'auto_approve_after_timeout' => 'boolean',
        'step_timeout_hours' => 'integer'
    ];

    public function steps()
    {
        return $this->hasMany(ThemeApprovalStep::class);
    }

    public function orderedSteps()
    {
        return $this->hasMany(ThemeApprovalStep::class)->orderBy('order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function shouldEscalateApproval($approval)
    {
        if (!$this->step_timeout_hours) {
            return false;
        }

        $currentStep = $approval->currentStep;
        $timeoutDate = $currentStep->created_at->addHours($this->step_timeout_hours);
        
        return Carbon::now()->greaterThan($timeoutDate);
    }

    public function getNextStep($currentStep)
    {
        return $this->orderedSteps()
            ->where('order', '>', $currentStep->order)
            ->first();
    }

    public function getEscalationApprovers()
    {
        if (empty($this->escalation_roles)) {
            return collect();
        }

        return User::role($this->escalation_roles)->get();
    }
}
