<?php

namespace App\Listeners;

use App\Events\LowStockDetected;
use App\Models\Product;
use App\Models\User;
use App\Notifications\LowStockNotification;
use App\Services\DashboardService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendLowStockNotification implements ShouldQueue
{
    use InteractsWithQueue;

    public function handle(LowStockDetected $event): void
    {
        app(DashboardService::class)->clearCache();

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
