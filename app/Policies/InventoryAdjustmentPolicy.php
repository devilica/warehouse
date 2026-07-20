<?php

namespace App\Policies;

use App\Models\InventoryAdjustment;
use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

class InventoryAdjustmentPolicy
{
    use ChecksPermissions;

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'inventory-adjustments.view');
    }

    public function view(User $user, InventoryAdjustment $model): bool
    {
        return $this->can($user, 'inventory-adjustments.view');
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'inventory-adjustments.create');
    }

    public function update(User $user, InventoryAdjustment $model): bool
    {
        return $this->can($user, 'inventory-adjustments.update');
    }

    public function delete(User $user, InventoryAdjustment $model): bool
    {
        return $this->can($user, 'inventory-adjustments.delete');
    }
}