<?php

namespace App\Policies;

use App\Models\User;
use App\Models\DeductionType;
use Illuminate\Auth\Access\Response;

class DeductionTypePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('View Any Deduction Type');
    }

    public function view(User $user, DeductionType $model): bool
    {
        return $user->can('View Deduction Type');
    }

    public function create(User $user): bool
    {
        return $user->can('Create Deduction Type');
    }

    public function update(User $user, DeductionType $model): bool
    {
        return $user->can('Update Deduction Type');
    }

    public function delete(User $user, DeductionType $model): bool
    {
        return $user->can('Delete Deduction Type');
    }

    public function restore(User $user, DeductionType $model): bool
    {
        return $user->can('Restore Deduction Type');
    }

    public function forceDelete(User $user, DeductionType $model): bool
    {
        return $user->can('Force Delete Deduction Type');
    }
}