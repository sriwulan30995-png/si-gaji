<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Attendance;
use Illuminate\Auth\Access\Response;

class AttendancePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('View Any Attendance');
    }

    public function view(User $user, Attendance $model): bool
    {
        return $user->can('View Attendance');
    }

    public function create(User $user): bool
    {
        return $user->can('Create Attendance');
    }

    public function update(User $user, Attendance $model): bool
    {
        return $user->can('Update Attendance');
    }

    public function delete(User $user, Attendance $model): bool
    {
        return $user->can('Delete Attendance');
    }

    public function restore(User $user, Attendance $model): bool
    {
        return $user->can('Restore Attendance');
    }

    public function forceDelete(User $user, Attendance $model): bool
    {
        return $user->can('Force Delete Attendance');
    }
}