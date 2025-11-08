<?php

namespace App\Policies;

use App\Models\Location; // <-- Ganti ini
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LocationPolicy
{
    use HandlesAuthorization;

    /**
     * Izinkan SEMUA aksi untuk admin.
     */
    public function before(User $user, $ability)
    {
        if ($user->hasTeamRole('admin')) {
            return true;
        }
    }

    /**
     * Tentukan apakah user bisa melihat DAFTAR lokasi.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasTeamRole('staff');
    }

    /**
     * Tentukan apakah user bisa melihat DETAIL lokasi.
     */
    public function view(User $user, Location $location): bool
    {
        return $user->hasTeamRole('staff');
    }

    /**
     * Tentukan apakah user boleh MEMBUAT lokasi.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Tentukan apakah user boleh MENGEDIT lokasi.
     */
    public function update(User $user, Location $location): bool
    {
        return false;
    }

    /**
     * Tentukan apakah user boleh MENGHAPUS lokasi.
     */
    public function delete(User $user, Location $location): bool 
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Location $location): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Location $location): bool
    {
        return false;
    }
}
