<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VersionRestorationLog;
use Illuminate\Auth\Access\HandlesAuthorization;

class RestorationLogPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('view restoration logs');
    }

    public function view(User $user, VersionRestorationLog $log)
    {
        return $user->hasPermissionTo('view restoration logs');
    }
}