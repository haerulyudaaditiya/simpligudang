<?php

namespace App\Policies;

use App\Models\Category;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CategoryPolicy
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
     * Tentukan apakah user bisa melihat DAFTAR kategori.
     * (Staff boleh lihat)
     */
    public function viewAny(User $user): bool
    {
        return $user->hasTeamRole('staff');
    }

    /**
     * Tentukan apakah user bisa melihat DETAIL kategori.
     * (Staff boleh lihat)
     */
    public function view(User $user, Category $category): bool
    {
        return $user->hasTeamRole('staff');
    }

    /**
     * Tentukan apakah user boleh MEMBUAT kategori.
     * (Staff TIDAK Boleh)
     */
    public function create(User $user): bool
    {
        return false; 
    }

    /**
     * Tentukan apakah user boleh MENGEDIT kategori.
     * (Staff TIDAK Boleh)
     */
    public function update(User $user, Category $category): bool
    {
        return false;
    }

    /**
     * Tentukan apakah user boleh MENGHAPUS kategori.
     * (Staff TIDAK Boleh)
     */
    public function delete(User $user, Category $category): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Category $category): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Category $category): bool
    {
        return false;
    }
}
