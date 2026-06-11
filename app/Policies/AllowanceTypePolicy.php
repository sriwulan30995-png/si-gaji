<?php

namespace App\Policies;

use App\Models\User;
use App\Models\AllowanceType;
use Illuminate\Auth\Access\Response;

class AllowanceTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('View Any Allowance Type');
    }

    public function view(User $user, AllowanceType $model): bool
    {
        return $user->can('View Allowance Type');
    }

    public function create(User $user): bool
    {
        return $user->can('Create Allowance Type');
    }

    public function update(User $user, AllowanceType $model): bool
    {
        return $user->can('Update Allowance Type');
    }

    public function delete(User $user, AllowanceType $model): bool
    {
        return $user->can('Delete Allowance Type');
    }

    public function restore(User $user, AllowanceType $model): bool
    {
        return $user->can('Restore Allowance Type');
    }

    public function forceDelete(User $user, AllowanceType $model): bool
    {
        return $user->can('Force Delete Allowance Type');
    }
}