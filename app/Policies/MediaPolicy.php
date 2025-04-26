<?php

namespace App\Policies;

use App\Models\Media;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MediaPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the media.
     */
    public function view(User $user, Media $media): bool
    {
        return $media->user_id === $user->id;
    }

    /**
     * Determine whether the user can create media.
     */
    public function create(User $user): bool
    {
        return true; // All authenticated users can create media
    }

    /**
     * Determine whether the user can update the media.
     */
    public function update(User $user, Media $media): bool
    {
        return $media->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the media.
     */
    public function delete(User $user, Media $media): bool
    {
        return $media->user_id === $user->id;
    }

    /**
     * Determine whether the user can add media to collections.
     */
    public function addToCollection(User $user, Media $media): bool
    {
        return $media->user_id === $user->id;
    }

    /**
     * Determine whether the user can remove media from collections.
     */
    public function removeFromCollection(User $user, Media $media): bool
    {
        return $media->user_id === $user->id;
    }
}
