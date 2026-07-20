<?php

namespace App\Policies;

use App\Models\PurchaseOrder;
use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

class PurchaseOrderPolicy
{
    use ChecksPermissions;

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'purchase-orders.view');
    }

    public function view(User $user, PurchaseOrder $model): bool
    {
        return $this->can($user, 'purchase-orders.view');
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'purchase-orders.create');
    }

    public function update(User $user, PurchaseOrder $model): bool
    {
        return $this->can($user, 'purchase-orders.update');
    }

    public function delete(User $user, PurchaseOrder $model): bool
    {
        return $this->can($user, 'purchase-orders.delete');
    }
}