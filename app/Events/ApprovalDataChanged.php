<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

class ApprovalDataChanged
{
    use Dispatchable;

    public function __construct(
        public ?int $workflowId = null
    ) {}
}
