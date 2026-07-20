<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\InventoryAdjustment\StoreInventoryAdjustmentRequest;
use App\Http\Resources\InventoryAdjustmentResource;
use App\Models\InventoryAdjustment;
use App\Services\InventoryAdjustmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;

class InventoryAdjustmentController extends ApiController
{
    public function __construct(private InventoryAdjustmentService $service) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', InventoryAdjustment::class);
        return $this->success(InventoryAdjustmentResource::collection(QueryBuilder::for(InventoryAdjustment::class)->paginate()));
    }

    public function show(InventoryAdjustment $inventoryAdjustment): JsonResponse
    {
        $this->authorize('view', $inventoryAdjustment);
        return $this->success(new InventoryAdjustmentResource($inventoryAdjustment->load('items.product')));
    }

    public function store(StoreInventoryAdjustmentRequest $request): JsonResponse
    {
        $this->authorize('create', InventoryAdjustment::class);
        $adjustment = DB::transaction(function () use ($request) {
            $data = $request->validated();
            $items = $data['items'];
            unset($data['items']);
            $adj = InventoryAdjustment::create($data + ['status' => 'pending']);
            foreach ($items as $item) {
                $adj->items()->create($item);
            }
            return $adj->load('items.product');
        });
        return $this->success(new InventoryAdjustmentResource($adjustment), null, 201);
    }

    public function approve(InventoryAdjustment $inventoryAdjustment): JsonResponse
    {
        $this->authorize('update', $inventoryAdjustment);
        return $this->success(new InventoryAdjustmentResource($this->service->approve($inventoryAdjustment)));
    }
}