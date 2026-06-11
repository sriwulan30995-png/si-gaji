<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PositionAllowance;
use Illuminate\Auth\Access\Response;

class PositionAllowancePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('View Any Position Allowance');
    }

    public function view(User $user, PositionAllowance $model): bool
    {
        return $user->can('View Position Allowance');
    }

    public function create(User $user): bool
    {
        return $user->can('Create Position Allowance');
    }

    public function update(User $user, PositionAllowance $model): bool
    {
        return $user->can('Update Position Allowance');
    }

    public function delete(User $user, PositionAllowance $model): bool
    {
        return $user->can('Delete Position Allowance');
    }

    public function restore(User $user, PositionAllowance $model): bool
    {
        return $user->can('Restore Position Allowance');
    }

    public function forceDelete(User $user, PositionAllowance $model): bool
    {
        return $user->can('Force Delete Position Allowance');
    }
}