<?php

namespace App\Policies;

use App\Models\Log;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LogPolicy
{
    use HandlesAuthorization;

    // 'admin' ditangani oleh 'before'
    public function before(User $user, $ability)
    {
        if ($user->hasTeamRole('admin')) {
            return true;
        }
    }

    // Staff TIDAK BOLEH melihat daftar Log global
    public function viewAny(User $user): bool
    {
        return false;
    }

    // Izinkan staff melihat log individual (untuk relation manager)
    public function view(User $user, Log $log): bool
    {
        return $user->hasTeamRole('staff');
    }

    // TIDAK ADA YANG BOLEH MEMBUAT LOG MANUAL
    public function create(User $user): bool
    {
        return false;
    }

    // TIDAK ADA YANG BOLEH MENGEDIT LOG
    public function update(User $user, Log $log): bool
    {
        return false;
    }

    // TIDAK ADA YANG BOLEH MENGHAPUS LOG
    public function delete(User $user, Log $log): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Log $log): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Log $log): bool
    {
        return false;
    }
}