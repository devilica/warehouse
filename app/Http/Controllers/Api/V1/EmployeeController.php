<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use Illuminate\Http\JsonResponse;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;

class EmployeeController extends ApiController
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Employee::class);
        $items = QueryBuilder::for(Employee::class)
            ->allowedFilters([AllowedFilter::partial('first_name'), AllowedFilter::partial('last_name')])
            ->allowedIncludes([AllowedInclude::relationship('department'), AllowedInclude::relationship('user')])
            ->allowedSorts(['created_at', 'id'])
            ->paginate(request('per_page', 15));

        return $this->success(EmployeeResource::collection($items));
    }

    public function show(Employee $employee): JsonResponse
    {
        $this->authorize('view', $employee);
        return $this->success(new EmployeeResource($employee->loadMissing([])));
    }
    
    public function store(Employee\StoreEmployeeRequest $request): JsonResponse
    {
        $this->authorize('create', Employee::class);
        $model = Employee::create($request->validated());
        return $this->success(new EmployeeResource($model), null, 201);
    }
    
    public function update(Employee\UpdateEmployeeRequest $request, Employee $employee): JsonResponse
    {
        $this->authorize('update', $employee);
        $employee->update($request->validated());
        return $this->success(new EmployeeResource($employee->fresh()));
    }
    
    public function destroy(Employee $employee): JsonResponse
    {
        $this->authorize('delete', $employee);
        $employee->delete();
        return $this->success(null, null, 204);
    }
}