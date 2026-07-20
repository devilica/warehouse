<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\WarehouseShelfResource;
use App\Models\Warehouse;
use App\Models\WarehouseShelf;
use App\Models\WarehouseZone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WarehouseShelfController extends ApiController
{
    public function index(Warehouse $warehouse, WarehouseZone $zone): JsonResponse
    {
        $this->authorize('view', $warehouse);
        return $this->success(WarehouseShelfResource::collection($zone->shelves()->paginate()));
    }

    public function store(Request $request, Warehouse $warehouse, WarehouseZone $zone): JsonResponse
    {
        $this->authorize('update', $warehouse);
        $shelf = $zone->shelves()->create($request->validate(['name' => 'required|string', 'code' => 'required|string']));
        return $this->success(new WarehouseShelfResource($shelf), null, 201);
    }

    public function update(Request $request, Warehouse $warehouse, WarehouseZone $zone, WarehouseShelf $shelf): JsonResponse
    {
        $this->authorize('update', $warehouse);
        $shelf->update($request->validate(['name' => 'sometimes|string', 'code' => 'sometimes|string']));
        return $this->success(new WarehouseShelfResource($shelf));
    }

    public function destroy(Warehouse $warehouse, WarehouseZone $zone, WarehouseShelf $shelf): JsonResponse
    {
        $this->authorize('update', $warehouse);
        $shelf->delete();
        return $this->success(null, null, 204);
    }
}