<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\AuditLogController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\EmployeeController;
use App\Http\Controllers\Api\V1\GoodsReceiptController;
use App\Http\Controllers\Api\V1\InventoryAdjustmentController;
use App\Http\Controllers\Api\V1\InventoryCountController;
use App\Http\Controllers\Api\V1\InventoryTransactionController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\ProductCategoryController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\PurchaseOrderController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\StockLevelController;
use App\Http\Controllers\Api\V1\StockTransferController;
use App\Http\Controllers\Api\V1\SupplierController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\WarehouseController;
use App\Http\Controllers\Api\V1\WarehouseLocationController;
use App\Http\Controllers\Api\V1\WarehouseShelfController;
use App\Http\Controllers\Api\V1\WarehouseZoneController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::put('auth/profile', [AuthController::class, 'updateProfile']);
        Route::put('auth/password', [AuthController::class, 'changePassword']);

        Route::apiResource('users', UserController::class);
        Route::put('users/{user}/roles', [RoleController::class, 'syncUserRoles']);
        Route::get('roles', [RoleController::class, 'index']);
        Route::get('roles/{role}', [RoleController::class, 'show']);
        Route::apiResource('employees', EmployeeController::class);
        Route::apiResource('suppliers', SupplierController::class);
        Route::apiResource('warehouses', WarehouseController::class);
        Route::apiResource('warehouses.zones', WarehouseZoneController::class)->shallow();
        Route::apiResource('warehouses.zones.shelves', WarehouseShelfController::class)->shallow();
        Route::apiResource('warehouses.zones.shelves.locations', WarehouseLocationController::class)->shallow();
        Route::apiResource('categories', ProductCategoryController::class);
        Route::apiResource('products', ProductController::class);
        Route::get('products/by-barcode/{code}', [ProductController::class, 'byBarcode']);
        Route::post('products/{product}/barcodes/generate', [ProductController::class, 'generateBarcode']);

        Route::apiResource('inventory-transactions', InventoryTransactionController::class)->only(['index', 'show']);
        Route::apiResource('stock-levels', StockLevelController::class)->only(['index', 'show']);

        Route::apiResource('purchase-orders', PurchaseOrderController::class)->except(['update', 'destroy']);
        Route::post('purchase-orders/{purchase_order}/send', [PurchaseOrderController::class, 'send']);
        Route::post('purchase-orders/{purchase_order}/close', [PurchaseOrderController::class, 'close']);

        Route::apiResource('goods-receipts', GoodsReceiptController::class)->except(['update', 'destroy']);
        Route::post('goods-receipts/{goods_receipt}/confirm', [GoodsReceiptController::class, 'confirm']);

        Route::apiResource('inventory-adjustments', InventoryAdjustmentController::class)->except(['update', 'destroy']);
        Route::post('inventory-adjustments/{inventory_adjustment}/approve', [InventoryAdjustmentController::class, 'approve']);

        Route::apiResource('stock-transfers', StockTransferController::class)->except(['update', 'destroy']);
        Route::post('stock-transfers/{stock_transfer}/approve', [StockTransferController::class, 'approve']);
        Route::post('stock-transfers/{stock_transfer}/ship', [StockTransferController::class, 'ship']);
        Route::post('stock-transfers/{stock_transfer}/receive', [StockTransferController::class, 'receive']);
        Route::post('stock-transfers/{stock_transfer}/complete', [StockTransferController::class, 'complete']);

        Route::apiResource('inventory-counts', InventoryCountController::class)->except(['update', 'destroy']);
        Route::post('inventory-counts/{inventory_count}/start', [InventoryCountController::class, 'start']);
        Route::post('inventory-counts/{inventory_count}/finalize', [InventoryCountController::class, 'finalize']);

        Route::prefix('dashboard')->group(function () {
            Route::get('summary', [DashboardController::class, 'summary']);
            Route::get('arrivals-today', [DashboardController::class, 'arrivalsToday']);
            Route::get('recent-activity', [DashboardController::class, 'recentActivity']);
            Route::get('warehouse-stats', [DashboardController::class, 'warehouseStats']);
            Route::get('employee-activity', [DashboardController::class, 'employeeActivity']);
            Route::get('order-trends', [DashboardController::class, 'orderTrends']);
        });

        Route::get('search', SearchController::class);
        Route::get('reports/{type}', ReportController::class);

        Route::get('notifications', [NotificationController::class, 'index']);
        Route::post('notifications/{id}/read', [NotificationController::class, 'markRead']);
        Route::post('notifications/read-all', [NotificationController::class, 'markAllRead']);

        Route::apiResource('audit-logs', AuditLogController::class)->only(['index', 'show']);
    });
});