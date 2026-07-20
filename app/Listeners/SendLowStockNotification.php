<?php

namespace App\Listeners;

use App\Events\LowStockDetected;
use App\Models\Product;
use App\Models\User;
use App\Notifications\LowStockNotification;
use App\Services\DashboardService;
use Illuminate\Support\Facades\Notification;

class SendLowStockNotification
{
    public function __construct(private DashboardService $dashboardService) {}

    public function handle(LowStockDetected $event): void
    {
        $this->dashboardService->clearCache();

        $product = Product::find($event->product_id);

        if (! $product) {
            return;
        }

        $recipients = User::role(['warehouse-manager', 'purchasing-manager', 'administrator'])->get();

        Notification::send($recipients, new LowStockNotification(
            $product,
            (int) $event->warehouse_id,
            (float) $event->current_quantity,
        ));
    }
}
