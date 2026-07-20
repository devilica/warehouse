<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends ApiController
{
    public function __construct(private SearchService $searchService) {}

    public function __invoke(Request $request): JsonResponse
    {
        $request->validate(['q' => 'required|string|min:2']);
        return $this->success($this->searchService->search($request->q));
    }
}