<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\ProductCategoryResource;
use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;

class ProductCategoryController extends ApiController
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', ProductCategory::class);
        $items = QueryBuilder::for(ProductCategory::class)
            ->allowedFilters([$this->searchFilter('name'), AllowedFilter::partial('name')])
            ->allowedIncludes([AllowedInclude::relationship('products')])
            ->allowedSorts(['name', 'created_at', 'id'])
            ->paginate($this->perPage());

        return $this->success(ProductCategoryResource::collection($items));
    }

    public function show(ProductCategory $category): JsonResponse
    {
        $this->authorize('view', $category);
        return $this->success(new ProductCategoryResource($category->loadMissing([])));
    }
    
    
    
    public function destroy(ProductCategory $category): JsonResponse
    {
        $this->authorize('delete', $category);
        $category->delete();
        return $this->success(null, null, 204);
    }
}