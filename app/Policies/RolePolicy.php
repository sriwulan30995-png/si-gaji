<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Role;
use Illuminate\Auth\Access\Response;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('View Any Role');
    }

    public function view(User $user, Role $model): bool
    {
        return $user->can('View Role');
    }

    public function create(User $user): bool
    {
        return $user->can('Create Role');
    }

    public function update(User $user, Role $model): bool
    {
        return $user->can('Update Role');
    }

    public function delete(User $user, Role $model): bool
    {
        return $user->can('Delete Role');
    }

    public function restore(User $user, Role $model): bool
    {
        return $user->can('Restore Role');
    }

    public function forceDelete(User $user, Role $model): bool
    {
        return $user->can('Force Delete Role');
    }
}