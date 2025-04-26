<?php

namespace App\Notifications;

use App\Models\ApprovalStep;

class ContentRejectedNotification extends ContentApprovalNotification
{
    public function __construct(ApprovalStep $step)
    {
        parent::__construct($step, self::TYPE_CONTENT_REJECTED);
    }
}
