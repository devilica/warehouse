<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Warehouse\StoreWarehouseLocationRequest;
use App\Http\Requests\Warehouse\UpdateWarehouseLocationRequest;
use App\Http\Resources\WarehouseLocationResource;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use App\Models\WarehouseShelf;
use App\Models\WarehouseZone;
use Illuminate\Http\JsonResponse;

class WarehouseLocationController extends ApiController
{
    public function index(Warehouse $warehouse, WarehouseZone $zone, WarehouseShelf $shelf): JsonResponse
    {
        $this->authorize('view', $warehouse);
        return $this->success(WarehouseLocationResource::collection($shelf->locations()->paginate($this->perPage())));
    }

    public function store(StoreWarehouseLocationRequest $request, Warehouse $warehouse, WarehouseZone $zone, WarehouseShelf $shelf): JsonResponse
    {
        $this->authorize('update', $warehouse);
        $location = $shelf->locations()->create($request->validated() + [
            'warehouse_id' => $shelf->zone->warehouse_id,
        ]);
        return $this->success(new WarehouseLocationResource($location), null, 201);
    }

    public function update(UpdateWarehouseLocationRequest $request, WarehouseLocation $location): JsonResponse
    {
        $this->authorize('update', $location->shelf->zone->warehouse);
        $location->update($request->validated());
        return $this->success(new WarehouseLocationResource($location));
    }

    public function destroy(WarehouseLocation $location): JsonResponse
    {
        $this->authorize('update', $location->shelf->zone->warehouse);
        $location->delete();
        return $this->success(null, null, 204);
    }
}