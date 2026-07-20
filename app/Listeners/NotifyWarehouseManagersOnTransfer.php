<?php

namespace App\Listeners;

use App\Events\StockTransferCompleted;
use App\Services\DashboardService;

class NotifyWarehouseManagersOnTransfer
{
    public function __construct(private DashboardService $dashboardService) {}

    public function handle(StockTransferCompleted $event): void
    {
        $this->dashboardService->clearCache();
    }
}
