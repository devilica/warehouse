<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\AuditLogResource;
use App\Models\AuditLog;
use Illuminate\Http\JsonResponse;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;

class AuditLogController extends ApiController
{
    public function index(): JsonResponse
    {
        $this->authorize('viewAny', AuditLog::class);
        $items = QueryBuilder::for(AuditLog::class)
            ->allowedFilters([AllowedFilter::exact('action')])
            ->allowedIncludes([AllowedInclude::relationship('user')])
            ->allowedSorts(['created_at', 'id'])
            ->paginate(request('per_page', 15));

        return $this->success(AuditLogResource::collection($items));
    }

    public function show(AuditLog $auditLog): JsonResponse
    {
        $this->authorize('view', $auditLog);
        return $this->success(new AuditLogResource($auditLog->loadMissing([])));
    }
    
    
    
}