<?php

namespace App\Services;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\StockLevel;
use Illuminate\Support\Facades\Cache;

class DashboardService
{
    private const TTL = 300;

    public function clearCache(): void
    {
        Cache::tags(['dashboard'])->flush();
    }

    public function summary(): array
    {
        return Cache::tags(['dashboard'])->remember('dashboard.summary', self::TTL, function () {
            return [
                'stock_value' => StockLevel::join('products', 'products.id', '=', 'stock_levels.product_id')
                    ->selectRaw('SUM(stock_levels.quantity * COALESCE(products.cost_price, 0)) as value')
                    ->value('value') ?? 0,
                'low_stock_count' => Product::whereColumn('reorder_level', '>=', function ($q) {
                    $q->from('stock_levels')->selectRaw('SUM(quantity)')->whereColumn('product_id', 'products.id');
                })->count(),
                'out_of_stock_count' => Product::whereDoesntHave('stockLevels', fn ($q) => $q->where('quantity', '>', 0))->count(),
                'pending_purchase_orders' => PurchaseOrder::whereIn('status', ['draft', 'sent'])->count(),
            ];
        });
    }

    public function arrivalsToday(): array
    {
        return Cache::tags(['dashboard'])->remember('dashboard.arrivals', self::TTL, fn () =>
            PurchaseOrder::whereDate('expected_delivery_date', today())->with('supplier')->get()->toArray()
        );
    }

    public function recentActivity(): array
    {
        return InventoryTransaction::with(['product', 'warehouse', 'user'])
            ->latest()->limit(20)->get()->toArray();
    }

    public function warehouseStats(): array
    {
        return StockLevel::selectRaw('warehouse_id, SUM(quantity) as total_quantity, COUNT(DISTINCT product_id) as product_count')
            ->groupBy('warehouse_id')->with('warehouse')->get()->toArray();
    }

    public function employeeActivity(): array
    {
        return InventoryTransaction::selectRaw('user_id, COUNT(*) as movements')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('user_id')->with('user')->get()->toArray();
    }
}