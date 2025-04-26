<?php

namespace App\Policies;

use App\Models\User;
use App\Models\ErrorResolutionStep;
use App\Models\ErrorResolutionWorkflow;
use Illuminate\Auth\Access\HandlesAuthorization;

class StepPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user, ErrorResolutionWorkflow $workflow)
    {
        return $user->can('view', $workflow);
    }

    public function create(User $user, ErrorResolutionWorkflow $workflow)
    {
        return $user->can('update', $workflow);
    }

    public function update(User $user, ErrorResolutionStep $step, ErrorResolutionWorkflow $workflow)
    {
        return $user->can('update', $workflow) && 
               $step->workflow_id === $workflow->id;
    }

    public function delete(User $user, ErrorResolutionStep $step, ErrorResolutionWorkflow $workflow)
    {
        return $user->can('update', $workflow) && 
               $step->workflow_id === $workflow->id;
    }
}