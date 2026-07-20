<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\InventoryTransactionResource;
use App\Models\InventoryTransaction;
use Illuminate\Http\JsonResponse;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;

class InventoryTransactionController extends ApiController
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', InventoryTransaction::class);
        $items = QueryBuilder::for(InventoryTransaction::class)
            ->allowedFilters([AllowedFilter::exact('type'), AllowedFilter::exact('warehouse_id')])
            ->allowedIncludes([AllowedInclude::relationship('product'), AllowedInclude::relationship('warehouse')])
            ->allowedSorts(['created_at', 'id'])
            ->paginate(request('per_page', 15));

        return $this->success(InventoryTransactionResource::collection($items));
    }

    public function show(InventoryTransaction $inventoryTransaction): JsonResponse
    {
        $this->authorize('view', $inventoryTransaction);
        return $this->success(new InventoryTransactionResource($inventoryTransaction->loadMissing([])));
    }
    
    
    
}