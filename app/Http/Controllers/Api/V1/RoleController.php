<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\RoleResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
}
