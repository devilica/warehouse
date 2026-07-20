<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;

class UserController extends ApiController
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', User::class);
        $items = QueryBuilder::for(User::class)
            ->allowedFilters([AllowedFilter::partial('name'), AllowedFilter::partial('email')])
            ->allowedIncludes([AllowedInclude::relationship('roles')])
            ->allowedSorts(['created_at', 'id'])
            ->paginate(request('per_page', 15));

        return $this->success(UserResource::collection($items));
    }

    public function show(User $user): JsonResponse
    {
        $this->authorize('view', $user);

        return $this->success(new UserResource($user->loadMissing(['roles'])));
    }

    public function store(StoreUserRequest $request): JsonResponse
    {
        $this->authorize('create', User::class);

        $data = $request->validated();
        $role = Arr::pull($data, 'role');

        $user = User::create($data);

        if ($role) {
            $user->syncRoles([$role]);
        }

        return $this->success(new UserResource($user->load('roles')), null, 201);
    }

    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        $this->authorize('update', $user);

        $data = $request->validated();
        $role = Arr::pull($data, 'role');

        if (empty($data['password'])) {
            unset($data['password']);
        }

        $user->update($data);

        if ($role) {
            $user->syncRoles([$role]);
        }

        return $this->success(new UserResource($user->fresh()->load('roles')));
    }

    public function destroy(User $user): JsonResponse
    {
        $this->authorize('delete', $user);
        $user->delete();

        return $this->success(null, null, 204);
    }
}
