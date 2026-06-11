<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Payroll;
use Illuminate\Auth\Access\Response;

class PayrollPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('View Any Payroll');
    }

    public function view(User $user, Payroll $model): bool
    {
        return $user->can('View Payroll');
    }

    public function create(User $user): bool
    {
        return $user->can('Create Payroll');
    }

    public function update(User $user, Payroll $model): bool
    {
        return $user->can('Update Payroll');
    }

    public function delete(User $user, Payroll $model): bool
    {
        return $user->can('Delete Payroll');
    }

    public function restore(User $user, Payroll $model): bool
    {
        return $user->can('Restore Payroll');
    }

    public function forceDelete(User $user, Payroll $model): bool
    {
        return $user->can('Force Delete Payroll');
    }
}