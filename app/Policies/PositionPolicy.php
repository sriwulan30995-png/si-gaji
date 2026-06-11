<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Position;
use Illuminate\Auth\Access\Response;

class PositionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('View Any Position');
    }

    public function view(User $user, Position $model): bool
    {
        return $user->can('View Position');
    }

    public function create(User $user): bool
    {
        return $user->can('Create Position');
    }

    public function update(User $user, Position $model): bool
    {
        return $user->can('Update Position');
    }

    public function delete(User $user, Position $model): bool
    {
        return $user->can('Delete Position');
    }

    public function restore(User $user, Position $model): bool
    {
        return $user->can('Restore Position');
    }

    public function forceDelete(User $user, Position $model): bool
    {
        return $user->can('Force Delete Position');
    }
}