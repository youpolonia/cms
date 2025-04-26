<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThemeVersionApproval extends Model
{
    use HasFactory;

    protected $fillable = [
        'theme_version_id',
        'workflow_id',
        'submitted_by',
        'approved_by',
        'rejected_by',
        'status',
        'notes',
        'approved_at',
        'rejected_at',
        'completed_steps',
        'total_steps',
        'progress_percentage',
        'current_step_id'
    ];

    protected $casts = [
        'approved_at' => 'datetime',
        'progress_percentage' => 'float',
        'current_step_id' => 'integer'
    ];

    public function version()
    {
        return $this->belongsTo(ThemeVersion::class);
    }

    public function step()
    {
        return $this->belongsTo(ThemeApprovalStep::class);
    }

    public function currentStep()
    {
        return $this->belongsTo(ThemeApprovalStep::class, 'current_step_id');
    }

    public function workflow()
    {
        return $this->belongsTo(ThemeApprovalWorkflow::class, 'workflow_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function calculateProgress()
    {
        if ($this->total_steps > 0) {
            $this->progress_percentage = ($this->completed_steps / $this->total_steps) * 100;
        } else {
            $this->progress_percentage = 0;
        }
        return $this;
    }

    public function updateProgress($completed, $total)
    {
        $this->completed_steps = $completed;
        $this->total_steps = $total;
        
        // If we're tracking steps individually, update current step
        if ($this->workflow && $this->workflow->steps->isNotEmpty()) {
            $nextStep = $this->workflow->steps
                ->where('order', '>', $this->currentStep?->order ?? 0)
                ->first();
            
            $this->current_step_id = $nextStep?->id ?? $this->current_step_id;
        }
        
        return $this->calculateProgress()->save();
    }

    public function approveCurrentStep(User $approver)
    {
        if (!$this->currentStep) {
            return false;
        }

        $this->completed_steps++;
        $this->current_step_id = $this->workflow->steps
            ->where('order', '>', $this->currentStep->order)
            ->first()?->id;

        return $this->calculateProgress()->save();
    }

    public function getCurrentStepStatus()
    {
        return [
            'current_step' => $this->currentStep,
            'progress' => $this->getProgressStatus(),
            'is_complete' => $this->isComplete(),
            'is_rejected' => $this->isRejected()
        ];
    }

    public function getProgressStatus()
    {
        return [
            'completed' => $this->completed_steps,
            'total' => $this->total_steps,
            'percentage' => $this->progress_percentage,
            'status' => $this->progress_percentage >= 100 ? 'complete' : 'in-progress'
        ];
    }

    public function isComplete(): bool
    {
        return $this->progress_percentage >= 100;
    }

    public function isRejected(): bool
    {
        return $this->status === 'rejected';
    }

    public function sendApprovalNotifications()
    {
        if ($this->isComplete()) {
            // Notify theme owner and approvers of approval completion
            $this->version->theme->user->notify(
                new \App\Notifications\ThemeApprovalNotification(
                    $this->version,
                    \App\Notifications\ThemeApprovalNotification::TYPE_APPROVED
                )
            );

            foreach ($this->version->approvals as $approval) {
                if ($approval->approver) {
                    $approval->approver->notify(
                        new \App\Notifications\ThemeApprovalNotification(
                            $this->version,
                            \App\Notifications\ThemeApprovalNotification::TYPE_APPROVED
                        )
                    );
                }
            }
        } elseif ($this->isRejected()) {
            // Notify theme owner of rejection
            $this->version->theme->user->notify(
                new \App\Notifications\ThemeApprovalNotification(
                    $this->version,
                    \App\Notifications\ThemeApprovalNotification::TYPE_REJECTED
                )
            );
        }
    }

    protected static function booted()
    {
        static::updated(function ($approval) {
            $approval->sendApprovalNotifications();
        });
    }
}
