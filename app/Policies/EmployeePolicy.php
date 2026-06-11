<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Employee;
use Illuminate\Auth\Access\Response;

class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('View Any Employee');
    }

    public function view(User $user, Employee $model): bool
    {
        return $user->can('View Employee');
    }

    public function create(User $user): bool
    {
        return $user->can('Create Employee');
    }

    public function update(User $user, Employee $model): bool
    {
        return $user->can('Update Employee');
    }

    public function delete(User $user, Employee $model): bool
    {
        return $user->can('Delete Employee');
    }

    public function restore(User $user, Employee $model): bool
    {
        return $user->can('Restore Employee');
    }

    public function forceDelete(User $user, Employee $model): bool
    {
        return $user->can('Force Delete Employee');
    }
}