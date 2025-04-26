<?php

namespace App\Notifications;

use App\Models\ApprovalStep;

class StepStartedNotification extends ContentApprovalNotification
{
    public function __construct(ApprovalStep $step)
    {
        parent::__construct($step, self::TYPE_STEP_APPROVAL);
    }
}
