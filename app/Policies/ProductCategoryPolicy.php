<?php

namespace App\Policies;

use App\Models\ProductCategory;
use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

class ProductCategoryPolicy
{
    use ChecksPermissions;

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'categories.view');
    }

    public function view(User $user, ProductCategory $model): bool
    {
        return $this->can($user, 'categories.view');
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'categories.create');
    }

    public function update(User $user, ProductCategory $model): bool
    {
        return $this->can($user, 'categories.update');
    }

    public function delete(User $user, ProductCategory $model): bool
    {
        return $this->can($user, 'categories.delete');
    }
}