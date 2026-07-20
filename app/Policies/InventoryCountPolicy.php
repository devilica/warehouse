<?php

namespace App\Policies;

use App\Models\InventoryCount;
use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

class InventoryCountPolicy
{
    use ChecksPermissions;

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'inventory-counts.view');
    }

    public function view(User $user, InventoryCount $model): bool
    {
        return $this->can($user, 'inventory-counts.view');
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'inventory-counts.create');
    }

    public function update(User $user, InventoryCount $model): bool
    {
        return $this->can($user, 'inventory-counts.update');
    }

    public function delete(User $user, InventoryCount $model): bool
    {
        return $this->can($user, 'inventory-counts.delete');
    }
}