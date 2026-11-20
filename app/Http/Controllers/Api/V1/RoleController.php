<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class RoleController extends ApiController
{
    public function index(): JsonResponse
    {
        abort_unless(auth()->user()?->can('roles.view'), 403);

        $roles = Role::query()
            ->with('permissions')
            ->orderBy('name')
            ->get();

        return $this->success(RoleResource::collection($roles));
    }

    public function show(Role $role): JsonResponse
    {
        abort_unless(auth()->user()?->can('roles.view'), 403);

        return $this->success(new RoleResource($role->load('permissions')));
    }

    public function store(StoreRoleRequest $request): JsonResponse
    {
        abort_unless(auth()->user()?->can('roles.create'), 403);

        $data = $request->validated();
        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($data['permissions']);

        return $this->success(new RoleResource($role->load('permissions')), null, 201);
    }

    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        abort_unless(auth()->user()?->can('roles.update'), 403);

        $data = $request->validated();
        $this->guardSuperAdmin($role, $data['name'], $data['permissions']);
        $role->update(['name' => $data['name']]);
        $role->syncPermissions($data['permissions']);

        return $this->success(new RoleResource($role->load('permissions')));
    }

    public function syncUserRoles(Request $request, User $user): JsonResponse
    {
        abort_unless(auth()->user()?->can('roles.update'), 403);

        $validated = $request->validate([
            'roles' => ['required', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
        ]);

        $user->syncRoles($validated['roles']);

        return $this->success(new UserResource($user->load('roles')));
    }

    private function guardSuperAdmin(Role $role, string $name, array $permissions): void
    {
        if ($role->name !== 'super-admin') {
            return;
        }

        if ($name !== 'super-admin' || ! in_array('roles.view', $permissions, true)) {
            throw ValidationException::withMessages([
                'permissions' => ['The super-admin role must keep its name and the roles.view permission.'],
            ]);
        }
    }
}
