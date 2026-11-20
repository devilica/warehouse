<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Warehouse\StoreWarehouseShelfRequest;
use App\Http\Requests\Warehouse\UpdateWarehouseShelfRequest;
use App\Http\Resources\WarehouseShelfResource;
use App\Models\Warehouse;
use App\Models\WarehouseShelf;
use App\Models\WarehouseZone;
use Illuminate\Http\JsonResponse;

class WarehouseShelfController extends ApiController
{
    public function index(Warehouse $warehouse, WarehouseZone $zone): JsonResponse
    {
        $this->authorize('view', $warehouse);
        return $this->success(WarehouseShelfResource::collection($zone->shelves()->paginate($this->perPage())));
    }

    public function store(StoreWarehouseShelfRequest $request, Warehouse $warehouse, WarehouseZone $zone): JsonResponse
    {
        $this->authorize('update', $warehouse);
        $shelf = $zone->shelves()->create($request->validated());
        return $this->success(new WarehouseShelfResource($shelf), null, 201);
    }

    public function update(UpdateWarehouseShelfRequest $request, Warehouse $warehouse, WarehouseZone $zone, WarehouseShelf $shelf): JsonResponse
    {
        $this->authorize('update', $warehouse);
        $shelf->update($request->validated());
        return $this->success(new WarehouseShelfResource($shelf));
    }

    public function destroy(Warehouse $warehouse, WarehouseZone $zone, WarehouseShelf $shelf): JsonResponse
    {
        $this->authorize('update', $warehouse);
        $shelf->delete();
        return $this->success(null, null, 204);
    }
}