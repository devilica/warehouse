<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Warehouse\StoreWarehouseZoneRequest;
use App\Http\Requests\Warehouse\UpdateWarehouseZoneRequest;
use App\Http\Resources\WarehouseZoneResource;
use App\Models\Warehouse;
use App\Models\WarehouseZone;
use Illuminate\Http\JsonResponse;

class WarehouseZoneController extends ApiController
{
    public function index(Warehouse $warehouse): JsonResponse
    {
        $this->authorize('view', $warehouse);
        return $this->success(WarehouseZoneResource::collection($warehouse->zones()->paginate($this->perPage())));
    }

    public function store(StoreWarehouseZoneRequest $request, Warehouse $warehouse): JsonResponse
    {
        $this->authorize('update', $warehouse);
        $zone = $warehouse->zones()->create($request->validated());
        return $this->success(new WarehouseZoneResource($zone), null, 201);
    }

    public function show(Warehouse $warehouse, WarehouseZone $zone): JsonResponse
    {
        $this->authorize('view', $warehouse);
        return $this->success(new WarehouseZoneResource($zone->load('shelves')));
    }

    public function update(UpdateWarehouseZoneRequest $request, WarehouseZone $zone): JsonResponse
    {
        $this->authorize('update', $zone->warehouse);
        $zone->update($request->validated());
        return $this->success(new WarehouseZoneResource($zone));
    }

    public function destroy(WarehouseZone $zone): JsonResponse
    {
        $this->authorize('update', $zone->warehouse);
        $zone->delete();
        return $this->success(null, null, 204);
    }
}