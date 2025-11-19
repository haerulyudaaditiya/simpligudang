<?php

namespace App\Policies;

use App\Models\BorrowRequest;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class BorrowRequestPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasTeamRole('admin') || $user->hasTeamRole('staff');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, BorrowRequest $borrowRequest): bool
    {
        $membership = $user->teams()
            ->where('team_id', $borrowRequest->team_id)
            ->withPivot('role')
            ->first();

        if (! $membership) {
            return false;
        }

        $role = $membership->pivot->role;

        if ($role === 'admin') {
            return true;
        }

        if ($role === 'staff') {
            return $borrowRequest->user_id === $user->id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasTeamRole('staff');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, BorrowRequest $borrowRequest): bool
    {
        return $user->hasTeamRole('admin');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, BorrowRequest $borrowRequest): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, BorrowRequest $borrowRequest): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, BorrowRequest $borrowRequest): bool
    {
        return false;
    }
}
