<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Spatie\QueryBuilder\AllowedFilter;

abstract class ApiController extends Controller
{
    use AuthorizesRequests;

    protected function perPage(): int
    {
        return min(100, max(1, (int) request('per_page', 10)));
    }

    protected function searchFilter(string ...$columns): AllowedFilter
    {
        return AllowedFilter::callback('search', function ($query, $value) use ($columns) {
            $term = '%'.(is_array($value) ? implode(' ', $value) : $value).'%';
            $query->where(function ($inner) use ($columns, $term) {
                foreach ($columns as $index => $column) {
                    $method = $index === 0 ? 'where' : 'orWhere';
                    $inner->{$method}($column, 'like', $term);
                }
            });
        });
    }

    protected function success(mixed $data = null, ?string $message = 'OK', int $status = 200, array $extraMeta = []): JsonResponse
    {
        $meta = null;

        if ($data instanceof ResourceCollection && $data->resource instanceof LengthAwarePaginator) {
            $meta = $this->paginationMeta($data->resource);
            $data = $data->resolve(request());
        } elseif ($data instanceof LengthAwarePaginator) {
            $meta = $this->paginationMeta($data);
            $data = $data->items();
        }

        $payload = [
            'message' => $message,
            'data' => $data,
        ];

        if ($meta !== null || $extraMeta !== []) {
            $payload['meta'] = array_merge($meta ?? [], $extraMeta);
        }

        return response()->json($payload, $status);
    }

    protected function paginationMeta(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];
    }

    protected function error(string $message, int $status = 400, ?array $errors = null, ?string $code = null): JsonResponse
    {
        return response()->json(array_filter([
            'message' => $message,
            'errors' => $errors,
            'code' => $code,
        ]), $status);
    }
}