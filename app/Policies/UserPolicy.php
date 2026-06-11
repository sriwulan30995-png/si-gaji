<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('View Any User');
    }

    public function view(User $user, User $model): bool
    {
        return $user->can('View User');
    }

    public function create(User $user): bool
    {
        return $user->can('Create User');
    }

    public function update(User $user, User $model): bool
    {
        return $user->can('Update User');
    }

    public function delete(User $user, User $model): bool
    {
        return $user->can('Delete User');
    }

    public function restore(User $user, User $model): bool
    {
        return $user->can('Restore User');
    }

    public function forceDelete(User $user, User $model): bool
    {
        return $user->can('Force Delete User');
    }
}
