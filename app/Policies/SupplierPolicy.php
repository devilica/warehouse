<?php

namespace App\Policies;

use App\Models\Supplier;
use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

class SupplierPolicy
{
    use ChecksPermissions;

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'suppliers.view');
    }

    public function view(User $user, Supplier $model): bool
    {
        return $this->can($user, 'suppliers.view');
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'suppliers.create');
    }

    public function update(User $user, Supplier $model): bool
    {
        return $this->can($user, 'suppliers.update');
    }

    public function delete(User $user, Supplier $model): bool
    {
        return $this->can($user, 'suppliers.delete');
    }
}