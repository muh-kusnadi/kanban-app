<?php

namespace App\Policies;

use App\Models\User;

class RolePolicy
{
    protected function getUserPermissions(User $user)
    {
        return $user->role()
            ->with('permissions')
            ->get()
            ->pluck('permissions')
            ->flatten()
            ->pluck('name');
    }

    public function before(User $user)
    {
        if ($user->role && $user->role->name == 'admin') {
            return true;
        }

        return null;
    }

    public function viewAllRoles(User $user): bool
    {
        $permissions = $this->getUserPermissions($user);

        if ($permissions->contains('view-all-role')) {
            return true;
        }

        return false;
    }

    public function manageRoles(User $user): bool
    {
        $permissions = $this->getUserPermissions($user);

        if ($permissions->contains('manage-roles')) {
            return true;
        }

        return false;
    }
}
