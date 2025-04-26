<?php

namespace App\Policies;

use App\Models\DiffComment;
use App\Models\User;

class DiffCommentPolicy
{
    public function update(User $user, DiffComment $comment)
    {
        return $user->id === $comment->user_id;
    }

    public function delete(User $user, DiffComment $comment)
    {
        return $user->id === $comment->user_id || $user->hasRole('admin');
    }
}