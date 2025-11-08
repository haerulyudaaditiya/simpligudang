<?php

namespace App\Policies;

use App\Models\Item;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ItemPolicy
{
    use HandlesAuthorization;

    public function before(User $user, $ability)
    {
        if ($user->hasTeamRole('admin')) {
            return true;
        }
    }

    public function viewAny(User $user): bool
    {
        return $user->hasTeamRole('staff');
    }

    public function view(User $user, Item $item): bool
    {
        return $user->hasTeamRole('staff');
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Item $item): bool
    {
        return false;
    }

    public function delete(User $user, Item $item): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Item $item): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Item $item): bool
    {
        return false;
    }
}
