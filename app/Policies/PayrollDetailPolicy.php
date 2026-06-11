<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PayrollDetail;
use Illuminate\Auth\Access\Response;

class PayrollDetailPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('View Any Payroll Detail');
    }

    public function view(User $user, PayrollDetail $model): bool
    {
        return $user->can('View Payroll Detail');
    }

    public function create(User $user): bool
    {
        return $user->can('Create Payroll Detail');
    }

    public function update(User $user, PayrollDetail $model): bool
    {
        return $user->can('Update Payroll Detail');
    }

    public function delete(User $user, PayrollDetail $model): bool
    {
        return $user->can('Delete Payroll Detail');
    }

    public function restore(User $user, PayrollDetail $model): bool
    {
        return $user->can('Restore Payroll Detail');
    }

    public function forceDelete(User $user, PayrollDetail $model): bool
    {
        return $user->can('Force Delete Payroll Detail');
    }
}