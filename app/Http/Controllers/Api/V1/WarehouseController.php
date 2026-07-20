<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\WarehouseResource;
use App\Models\Warehouse;
use Illuminate\Http\JsonResponse;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;

class WarehouseController extends ApiController
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Warehouse::class);
        $items = QueryBuilder::for(Warehouse::class)
            ->allowedFilters([AllowedFilter::partial('name')])
            ->allowedIncludes([AllowedInclude::relationship('zones')])
            ->allowedSorts(['created_at', 'id'])
            ->paginate(request('per_page', 15));

        return $this->success(WarehouseResource::collection($items));
    }

    public function show(Warehouse $warehouse): JsonResponse
    {
        $this->authorize('view', $warehouse);
        return $this->success(new WarehouseResource($warehouse->loadMissing([])));
    }
    
    public function store(Warehouse\StoreWarehouseRequest $request): JsonResponse
    {
        $this->authorize('create', Warehouse::class);
        $model = Warehouse::create($request->validated());
        return $this->success(new WarehouseResource($model), null, 201);
    }
    
    public function update(Warehouse\UpdateWarehouseRequest $request, Warehouse $warehouse): JsonResponse
    {
        $this->authorize('update', $warehouse);
        $warehouse->update($request->validated());
        return $this->success(new WarehouseResource($warehouse->fresh()));
    }
    
    public function destroy(Warehouse $warehouse): JsonResponse
    {
        $this->authorize('delete', $warehouse);
        $warehouse->delete();
        return $this->success(null, null, 204);
    }
}