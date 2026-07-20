<?php

namespace App\Providers;

use App\Events\GoodsReceiptConfirmed;
use App\Events\LowStockDetected;
use App\Events\StockMovementRecorded;
use App\Events\StockTransferCompleted;
use App\Listeners\CheckLowStockOnMovement;
use App\Listeners\NotifyPurchasingOnGoodsReceipt;
use App\Listeners\NotifyWarehouseManagersOnTransfer;
use App\Listeners\SendLowStockNotification;
use App\Listeners\UpdateDashboardCacheOnStockMovement;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        StockMovementRecorded::class => [
            UpdateDashboardCacheOnStockMovement::class,
            CheckLowStockOnMovement::class,
        ],
        GoodsReceiptConfirmed::class => [
            NotifyPurchasingOnGoodsReceipt::class,
        ],
        LowStockDetected::class => [
            SendLowStockNotification::class,
        ],
        StockTransferCompleted::class => [
            NotifyWarehouseManagersOnTransfer::class,
        ],
    ];
}