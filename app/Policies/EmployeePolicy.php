<?php

namespace App\Policies;

use App\Models\Employee;
use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

class EmployeePolicy
{
    use ChecksPermissions;

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'employees.view');
    }

    public function view(User $user, Employee $model): bool
    {
        return $this->can($user, 'employees.view');
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'employees.create');
    }

    public function update(User $user, Employee $model): bool
    {
        return $this->can($user, 'employees.update');
    }

    public function delete(User $user, Employee $model): bool
    {
        return $this->can($user, 'employees.delete');
    }
}