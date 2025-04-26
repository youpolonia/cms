<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Category;
use Illuminate\Auth\Access\HandlesAuthorization;

class CategoryPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->can('view categories');
    }

    public function view(User $user, Category $category)
    {
        return $user->can('view categories');
    }

    public function create(User $user)
    {
        return $user->can('create categories');
    }

    public function update(User $user, Category $category)
    {
        return $user->can('edit categories');
    }

    public function delete(User $user, Category $category)
    {
        return $user->can('delete categories');
    }
}