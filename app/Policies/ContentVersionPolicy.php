<?php

namespace App\Policies;

use App\Models\ContentVersion;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContentVersionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->can('view content versions');
    }

    public function view(User $user, ContentVersion $version): bool
    {
        return $user->can('view content versions') && 
               ($user->id === $version->user_id || $user->hasRole('admin'));
    }

    public function create(User $user): bool
    {
        return $user->can('create content versions');
    }

    public function update(User $user, ContentVersion $version): bool
    {
        return $user->can('edit content versions') && 
               ($user->id === $version->user_id || $user->hasRole('admin')) &&
               $version->status !== 'published';
    }

    public function delete(User $user, ContentVersion $version): bool
    {
        return $user->can('delete content versions') && 
               ($user->id === $version->user_id || $user->hasRole('admin')) &&
               $version->status !== 'published';
    }

    public function restore(User $user, ContentVersion $version): bool
    {
        return $user->can('restore content versions') && 
               $user->hasRole('admin') &&
               $version->status === 'published';
    }

    public function approve(User $user): bool
    {
        return $user->can('approve content versions');
    }

    public function compare(User $user, ContentVersion $version): bool
    {
        return $user->can('compare content versions') && 
               ($user->id === $version->user_id || $user->hasRole('admin'));
    }
}
