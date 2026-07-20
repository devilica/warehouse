<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

class ProductPolicy
{
    use ChecksPermissions;

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'products.view');
    }

    public function view(User $user, Product $model): bool
    {
        return $this->can($user, 'products.view');
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'products.create');
    }

    public function update(User $user, Product $model): bool
    {
        return $this->can($user, 'products.update');
    }

    public function delete(User $user, Product $model): bool
    {
        return $this->can($user, 'products.delete');
    }
}