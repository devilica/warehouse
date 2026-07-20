<?php

namespace App\Policies;

use App\Models\StockTransfer;
use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

class StockTransferPolicy
{
    use ChecksPermissions;

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'stock-transfers.view');
    }

    public function view(User $user, StockTransfer $model): bool
    {
        return $this->can($user, 'stock-transfers.view');
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'stock-transfers.create');
    }

    public function update(User $user, StockTransfer $model): bool
    {
        return $this->can($user, 'stock-transfers.update');
    }

    public function delete(User $user, StockTransfer $model): bool
    {
        return $this->can($user, 'stock-transfers.delete');
    }
}