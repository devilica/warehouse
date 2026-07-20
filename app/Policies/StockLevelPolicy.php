<?php

namespace App\Policies;

use App\Models\StockLevel;
use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

class StockLevelPolicy
{
    use ChecksPermissions;

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'inventory.view');
    }

    public function view(User $user, StockLevel $model): bool
    {
        return $this->can($user, 'inventory.view');
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'inventory.adjust');
    }

    public function update(User $user, StockLevel $model): bool
    {
        return $this->can($user, 'inventory.adjust');
    }

    public function delete(User $user, StockLevel $model): bool
    {
        return $this->can($user, 'inventory.adjust');
    }
}