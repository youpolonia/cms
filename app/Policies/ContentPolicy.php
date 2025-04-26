<?php

namespace App\Policies;

use App\Models\Content;
use App\Models\User;

class ContentPolicy
{
    public function viewAnyTrashed(User $user)
    {
        return $user->can('content.delete');
    }

    public function restore(User $user, Content $content)
    {
        return $user->can('content.restore') && 
               $content->trashed();
    }

    public function forceDelete(User $user, Content $content)
    {
        return $user->can('content.force-delete') && 
               $content->trashed();
    }

    public function emptyTrash(User $user)
    {
        return $user->can('content.empty-trash');
    }
}
