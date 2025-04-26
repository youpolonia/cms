<?php

namespace App\Notifications;

use App\Models\ApprovalStep;

class WorkflowStartedNotification extends ContentApprovalNotification
{
    public function __construct(ApprovalStep $step)
    {
        parent::__construct($step, self::TYPE_WORKFLOW_STARTED);
    }
}
