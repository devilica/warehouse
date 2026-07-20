<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardController extends ApiController
{
    public function __construct(private DashboardService $dashboardService) {}

    public function summary(): JsonResponse
    {
        return $this->success($this->dashboardService->summary());
    }

    public function arrivalsToday(): JsonResponse
    {
        return $this->success($this->dashboardService->arrivalsToday());
    }

    public function recentActivity(): JsonResponse
    {
        return $this->success($this->dashboardService->recentActivity());
    }

    public function warehouseStats(): JsonResponse
    {
        return $this->success($this->dashboardService->warehouseStats());
    }

    public function employeeActivity(): JsonResponse
    {
        return $this->success($this->dashboardService->employeeActivity());
    }

    public function orderTrends(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'period' => 'sometimes|in:day,month',
        ]);

        return $this->success($this->dashboardService->orderTrends($validated['period'] ?? 'day'));
    }
}