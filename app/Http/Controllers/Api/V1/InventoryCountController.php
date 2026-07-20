<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\InventoryCount\StoreInventoryCountRequest;
use App\Http\Resources\InventoryCountResource;
use App\Models\InventoryCount;
use App\Services\InventoryCountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class InventoryCountController extends ApiController
{
    public function __construct(private InventoryCountService $service) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', InventoryCount::class);
        return $this->success(InventoryCountResource::collection(QueryBuilder::for(InventoryCount::class)->paginate()));
    }

    public function show(InventoryCount $inventoryCount): JsonResponse
    {
        $this->authorize('view', $inventoryCount);
        return $this->success(new InventoryCountResource($inventoryCount->load('items.product')));
    }

    public function store(StoreInventoryCountRequest $request): JsonResponse
    {
        $this->authorize('create', InventoryCount::class);
        $count = InventoryCount::create($request->validated() + ['status' => 'scheduled']);
        return $this->success(new InventoryCountResource($count), null, 201);
    }

    public function start(InventoryCount $inventoryCount): JsonResponse
    {
        $this->authorize('update', $inventoryCount);
        return $this->success(new InventoryCountResource($this->service->start($inventoryCount)));
    }

    public function finalize(Request $request, InventoryCount $inventoryCount): JsonResponse
    {
        $this->authorize('update', $inventoryCount);
        return $this->success(new InventoryCountResource(
            $this->service->finalize($inventoryCount, $request->boolean('auto_adjust'))
        ));
    }
}