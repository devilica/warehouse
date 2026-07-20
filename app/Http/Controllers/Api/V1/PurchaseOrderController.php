<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\PurchaseOrder\StorePurchaseOrderRequest;
use App\Http\Resources\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderService;
use Illuminate\Http\JsonResponse;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;

class PurchaseOrderController extends ApiController
{
    public function __construct(private PurchaseOrderService $service) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', PurchaseOrder::class);
        $items = QueryBuilder::for(PurchaseOrder::class)
            ->allowedFilters([AllowedFilter::exact('status'), AllowedFilter::exact('supplier_id')])
            ->allowedIncludes([AllowedInclude::relationship('supplier'), AllowedInclude::relationship('items')])
            ->paginate(request('per_page', 15));

        return $this->success(PurchaseOrderResource::collection($items));
    }

    public function show(PurchaseOrder $purchaseOrder): JsonResponse
    {
        $this->authorize('view', $purchaseOrder);
        return $this->success(new PurchaseOrderResource($purchaseOrder->load(['supplier', 'items.product'])));
    }

    public function store(StorePurchaseOrderRequest $request): JsonResponse
    {
        $this->authorize('create', PurchaseOrder::class);
        $order = $this->service->create($request->validated());
        return $this->success(new PurchaseOrderResource($order), null, 201);
    }

    public function send(PurchaseOrder $purchaseOrder): JsonResponse
    {
        $this->authorize('update', $purchaseOrder);
        return $this->success(new PurchaseOrderResource($this->service->send($purchaseOrder)));
    }

    public function close(PurchaseOrder $purchaseOrder): JsonResponse
    {
        $this->authorize('update', $purchaseOrder);
        return $this->success(new PurchaseOrderResource($this->service->close($purchaseOrder)));
    }
}