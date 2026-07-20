<?php

namespace App\Listeners;

use App\Events\GoodsReceiptConfirmed;
use App\Services\DashboardService;

class NotifyPurchasingOnGoodsReceipt
{
    public function __construct(private DashboardService $dashboardService) {}

    public function handle(GoodsReceiptConfirmed $event): void
    {
        $this->dashboardService->clearCache();
    }
}
