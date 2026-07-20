<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait ChecksPermissions
{
    protected function can(User $user, string $permission): bool
    {
        return $user->hasRole('super-admin') || $user->can($permission);
    }
}