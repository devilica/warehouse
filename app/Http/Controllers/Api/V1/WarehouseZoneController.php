<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\WarehouseZoneResource;
use App\Models\Warehouse;
use App\Models\WarehouseZone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WarehouseZoneController extends ApiController
{
    public function index(Warehouse $warehouse): JsonResponse
    {
        $this->authorize('view', $warehouse);
        return $this->success(WarehouseZoneResource::collection($warehouse->zones()->paginate()));
    }

    public function store(Request $request, Warehouse $warehouse): JsonResponse
    {
        $this->authorize('update', $warehouse);
        $zone = $warehouse->zones()->create($request->validate(['name' => 'required|string', 'code' => 'required|string']));
        return $this->success(new WarehouseZoneResource($zone), null, 201);
    }

    public function show(Warehouse $warehouse, WarehouseZone $zone): JsonResponse
    {
        $this->authorize('view', $warehouse);
        return $this->success(new WarehouseZoneResource($zone->load('shelves')));
    }

    public function update(Request $request, Warehouse $warehouse, WarehouseZone $zone): JsonResponse
    {
        $this->authorize('update', $warehouse);
        $zone->update($request->validate(['name' => 'sometimes|string', 'code' => 'sometimes|string']));
        return $this->success(new WarehouseZoneResource($zone));
    }

    public function destroy(Warehouse $warehouse, WarehouseZone $zone): JsonResponse
    {
        $this->authorize('update', $warehouse);
        $zone->delete();
        return $this->success(null, null, 204);
    }
}