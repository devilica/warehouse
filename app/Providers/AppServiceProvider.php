<?php

namespace App\Providers;

use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\GoodsReceipt;
use App\Models\InventoryAdjustment;
use App\Models\InventoryCount;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\PurchaseOrder;
use App\Models\StockLevel;
use App\Models\StockTransfer;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Policies\AuditLogPolicy;
use App\Policies\EmployeePolicy;
use App\Policies\GoodsReceiptPolicy;
use App\Policies\InventoryAdjustmentPolicy;
use App\Policies\InventoryCountPolicy;
use App\Policies\InventoryTransactionPolicy;
use App\Policies\ProductCategoryPolicy;
use App\Policies\ProductPolicy;
use App\Policies\PurchaseOrderPolicy;
use App\Policies\StockLevelPolicy;
use App\Policies\StockTransferPolicy;
use App\Policies\SupplierPolicy;
use App\Policies\UserPolicy;
use App\Policies\WarehousePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => UserPolicy::class,
        Employee::class => EmployeePolicy::class,
        Supplier::class => SupplierPolicy::class,
        Warehouse::class => WarehousePolicy::class,
        ProductCategory::class => ProductCategoryPolicy::class,
        Product::class => ProductPolicy::class,
        InventoryTransaction::class => InventoryTransactionPolicy::class,
        StockLevel::class => StockLevelPolicy::class,
        PurchaseOrder::class => PurchaseOrderPolicy::class,
        GoodsReceipt::class => GoodsReceiptPolicy::class,
        InventoryAdjustment::class => InventoryAdjustmentPolicy::class,
        StockTransfer::class => StockTransferPolicy::class,
        InventoryCount::class => InventoryCountPolicy::class,
        AuditLog::class => AuditLogPolicy::class,
    ];

    public function register(): void
    {
        $this->app->singleton(
            \Illuminate\Foundation\Console\ServeCommand::class,
            \App\Console\Commands\ServeCommand::class
        );
    }

    public function boot(): void
    {
        if ($vercelUrl = env('VERCEL_URL')) {
            $root = 'https://'.$vercelUrl;
            config(['app.url' => $root]);
            URL::forceRootUrl($root);
            URL::forceScheme('https');
        }

        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }

        Sanctum::usePersonalAccessTokenModel(\Laravel\Sanctum\PersonalAccessToken::class);
    }
}