<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Overtime;
use Illuminate\Auth\Access\Response;

class OvertimePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('View Any Overtime');
    }

    public function view(User $user, Overtime $model): bool
    {
        return $user->can('View Overtime');
    }

    public function create(User $user): bool
    {
        return $user->can('Create Overtime');
    }

    public function update(User $user, Overtime $model): bool
    {
        return $user->can('Update Overtime');
    }

    public function delete(User $user, Overtime $model): bool
    {
        return $user->can('Delete Overtime');
    }

    public function restore(User $user, Overtime $model): bool
    {
        return $user->can('Restore Overtime');
    }

    public function forceDelete(User $user, Overtime $model): bool
    {
        return $user->can('Force Delete Overtime');
    }
}