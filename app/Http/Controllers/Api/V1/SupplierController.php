<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;

class SupplierController extends ApiController
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Supplier::class);
        $items = QueryBuilder::for(Supplier::class)
            ->allowedFilters([$this->searchFilter('company_name', 'email'), AllowedFilter::partial('company_name'), AllowedFilter::exact('code')])
            ->allowedIncludes([AllowedInclude::relationship('contacts')])
            ->allowedSorts(['company_name', 'email', 'phone', 'created_at', 'id'])
            ->paginate($this->perPage());

        return $this->success(SupplierResource::collection($items));
    }

    public function show(Supplier $supplier): JsonResponse
    {
        $this->authorize('view', $supplier);
        return $this->success(new SupplierResource($supplier->loadMissing([])));
    }
    
    public function store(Supplier\StoreSupplierRequest $request): JsonResponse
    {
        $this->authorize('create', Supplier::class);
        $model = Supplier::create($request->validated());
        return $this->success(new SupplierResource($model), null, 201);
    }
    
    public function update(Supplier\UpdateSupplierRequest $request, Supplier $supplier): JsonResponse
    {
        $this->authorize('update', $supplier);
        $supplier->update($request->validated());
        return $this->success(new SupplierResource($supplier->fresh()));
    }
    
    public function destroy(Supplier $supplier): JsonResponse
    {
        $this->authorize('delete', $supplier);
        $supplier->delete();
        return $this->success(null, null, 204);
    }
}