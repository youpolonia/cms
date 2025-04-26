<?php

namespace App\Policies;

use App\Models\Block;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

class BlockPolicy
{
    public function view(User $user, Block $block)
    {
        return $block->user_id === $user->id ||
               $block->is_template ||
               Gate::forUser($user)->any(['admin', 'content-manager']);
    }

    public function update(User $user, Block $block)
    {
        if ($block->isLocked()) {
            return Gate::forUser($user)->any(['admin', 'content-manager']) ||
                   $user->hasPermissionTo('edit-locked-content');
        }

        return $block->user_id === $user->id ||
               Gate::forUser($user)->any(['admin', 'content-manager']);
    }

    public function delete(User $user, Block $block)
    {
        return $block->user_id === $user->id ||
               Gate::forUser($user)->any(['admin', 'content-manager']);
    }
}