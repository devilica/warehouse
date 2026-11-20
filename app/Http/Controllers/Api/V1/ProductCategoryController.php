<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\ProductCategory\StoreProductCategoryRequest;
use App\Http\Requests\ProductCategory\UpdateProductCategoryRequest;
use App\Http\Resources\ProductCategoryResource;
use App\Models\ProductCategory;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
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

    public function store(StoreProductCategoryRequest $request): JsonResponse
    {
        $this->authorize('create', ProductCategory::class);
        $data = $request->validated();
        $category = ProductCategory::create([
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['name']),
            'parent_id' => $data['parent_id'] ?? null,
        ]);

        return $this->success(new ProductCategoryResource($category), null, 201);
    }

    public function update(UpdateProductCategoryRequest $request, ProductCategory $category): JsonResponse
    {
        $this->authorize('update', $category);
        $data = $request->validated();
        $category->update([
            'name' => $data['name'],
            'slug' => $this->uniqueSlug($data['name'], $category->id),
            'parent_id' => $data['parent_id'] ?? null,
        ]);

        return $this->success(new ProductCategoryResource($category));
    }
    
    
    
    public function destroy(ProductCategory $category): JsonResponse
    {
        $this->authorize('delete', $category);
        $category->delete();
        return $this->success(null, null, 204);
    }

    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name) ?: 'category';
        $slug = $base;
        $suffix = 2;

        while (
            ProductCategory::query()
                ->where('slug', $slug)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->exists()
        ) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}