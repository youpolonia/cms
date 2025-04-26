<?php

namespace App\Policies;

use App\Models\User;
use App\Models\MediaCollection;
use Illuminate\Auth\Access\HandlesAuthorization;

class MediaCollectionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->can('view media collections');
    }

    public function view(User $user, MediaCollection $collection)
    {
        return $user->can('view media collections');
    }

    public function create(User $user)
    {
        return $user->can('create media collections');
    }

    public function update(User $user, MediaCollection $collection)
    {
        return $user->can('edit media collections');
    }

    public function delete(User $user, MediaCollection $collection)
    {
        return $user->can('delete media collections');
    }

    public function manageItems(User $user, MediaCollection $collection)
    {
        return $user->can('manage media collection items');
    }
}
