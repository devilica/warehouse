<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\StockLevelResource;
use App\Models\StockLevel;
use Illuminate\Http\JsonResponse;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;

class StockLevelController extends ApiController
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', StockLevel::class);
        $items = QueryBuilder::for(StockLevel::class)
            ->allowedFilters([AllowedFilter::exact('warehouse_id'), AllowedFilter::exact('product_id')])
            ->allowedIncludes([AllowedInclude::relationship('product'), AllowedInclude::relationship('warehouse')])
            ->allowedSorts(['created_at', 'id'])
            ->paginate(request('per_page', 15));

        return $this->success(StockLevelResource::collection($items));
    }

    public function show(StockLevel $stockLevel): JsonResponse
    {
        $this->authorize('view', $stockLevel);
        return $this->success(new StockLevelResource($stockLevel->loadMissing([])));
    }
    
    
    
}