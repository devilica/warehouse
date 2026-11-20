<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\InventoryCount\StoreInventoryCountRequest;
use App\Http\Resources\InventoryCountResource;
use App\Models\InventoryCount;
use App\Services\InventoryCountService;
use App\Support\DocumentNumber;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class InventoryCountController extends ApiController
{
    public function __construct(private InventoryCountService $service) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', InventoryCount::class);
        $items = QueryBuilder::for(InventoryCount::class)
            ->allowedFilters([AllowedFilter::exact('status')])
            ->allowedSorts(['status', 'created_at', 'id'])
            ->paginate($this->perPage());

        return $this->success(InventoryCountResource::collection($items));
    }

    public function show(InventoryCount $inventoryCount): JsonResponse
    {
        $this->authorize('view', $inventoryCount);
        return $this->success(new InventoryCountResource($inventoryCount->load(['warehouse', 'items.product', 'items.location'])));
    }

    public function store(StoreInventoryCountRequest $request): JsonResponse
    {
        $this->authorize('create', InventoryCount::class);
        $data = $request->validated();
        $count = InventoryCount::create([
            'warehouse_id' => $data['warehouse_id'],
            'type' => $data['type'] ?? 'full',
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'status' => 'scheduled',
            'number' => DocumentNumber::make('CNT-'),
        ]);
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