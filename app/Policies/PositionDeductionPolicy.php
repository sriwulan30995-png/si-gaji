<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PositionDeduction;
use Illuminate\Auth\Access\Response;

class PositionDeductionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('View Any Position Deduction');
    }

    public function view(User $user, PositionDeduction $model): bool
    {
        return $user->can('View Position Deduction');
    }

    public function create(User $user): bool
    {
        return $user->can('Create Position Deduction');
    }

    public function update(User $user, PositionDeduction $model): bool
    {
        return $user->can('Update Position Deduction');
    }

    public function delete(User $user, PositionDeduction $model): bool
    {
        return $user->can('Delete Position Deduction');
    }

    public function restore(User $user, PositionDeduction $model): bool
    {
        return $user->can('Restore Position Deduction');
    }

    public function forceDelete(User $user, PositionDeduction $model): bool
    {
        return $user->can('Force Delete Position Deduction');
    }
}