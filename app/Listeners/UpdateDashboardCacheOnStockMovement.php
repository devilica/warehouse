<?php

namespace App\Listeners;

use App\Events\StockMovementRecorded;
use App\Services\DashboardService;

class UpdateDashboardCacheOnStockMovement
{
    public function __construct(private DashboardService $dashboardService) {}

    public function handle(StockMovementRecorded $event): void
    {
        $this->dashboardService->clearCache();
    }
}
