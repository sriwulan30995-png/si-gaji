<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Permission;
use Illuminate\Auth\Access\Response;

class PermissionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('View Any Permission');
    }

    public function view(User $user, Permission $model): bool
    {
        return $user->can('View Permission');
    }

    public function create(User $user): bool
    {
        return $user->can('Create Permission');
    }

    public function update(User $user, Permission $model): bool
    {
        return $user->can('Update Permission');
    }

    public function delete(User $user, Permission $model): bool
    {
        return $user->can('Delete Permission');
    }

    public function restore(User $user, Permission $model): bool
    {
        return $user->can('Restore Permission');
    }

    public function forceDelete(User $user, Permission $model): bool
    {
        return $user->can('Force Delete Permission');
    }
}