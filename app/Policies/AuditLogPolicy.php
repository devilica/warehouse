<?php

namespace App\Policies;

use App\Models\AuditLog;
use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

class AuditLogPolicy
{
    use ChecksPermissions;

    public function viewAny(User $user): bool
    {
        return $this->can($user, 'audit.view');
    }

    public function view(User $user, AuditLog $model): bool
    {
        return $this->can($user, 'audit.view');
    }

    public function create(User $user): bool
    {
        return $this->can($user, 'audit.create');
    }

    public function update(User $user, AuditLog $model): bool
    {
        return $this->can($user, 'audit.update');
    }

    public function delete(User $user, AuditLog $model): bool
    {
        return $this->can($user, 'audit.view');
    }
}