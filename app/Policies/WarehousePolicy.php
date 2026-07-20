<?php

namespace App\Policies;

use App\Models\Warehouse;
use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

class WarehousePolicy
{
    use ChecksPermissions;

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'warehouses.view');
    }

    public function view(User $user, Warehouse $model): bool
    {
        return $this->can($user, 'warehouses.view');
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'warehouses.create');
    }

    public function update(User $user, Warehouse $model): bool
    {
        return $this->can($user, 'warehouses.update');
    }

    public function delete(User $user, Warehouse $model): bool
    {
        return $this->can($user, 'warehouses.delete');
    }
}