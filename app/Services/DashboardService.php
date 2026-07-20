<?php

namespace App\Services;

use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\StockLevel;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardService
{
    private const TTL = 300;

    private const CACHE_KEYS = [
        'dashboard.summary',
        'dashboard.arrivals',
        'dashboard.order-trends.day',
        'dashboard.order-trends.month',
    ];

    public function clearCache(): void
    {
        foreach (self::CACHE_KEYS as $key) {
            Cache::forget($key);
        }
    }

    public function summary(): array
    {
        return Cache::remember('dashboard.summary', self::TTL, function () {
            return [
                'stock_value' => StockLevel::join('products', 'products.id', '=', 'stock_levels.product_id')
                    ->selectRaw('SUM(stock_levels.quantity * COALESCE(products.purchase_price, 0)) as value')
                    ->value('value') ?? 0,
                'low_stock_count' => Product::where('min_stock', '>', 0)
                    ->where('min_stock', '>=', function ($q) {
                        $q->from('stock_levels')
                            ->selectRaw('COALESCE(SUM(quantity), 0)')
                            ->whereColumn('stock_levels.product_id', 'products.id');
                    })
                    ->count(),
                'out_of_stock_count' => Product::whereDoesntHave('stockLevels', fn ($q) => $q->where('quantity', '>', 0))->count(),
                'pending_purchase_orders' => PurchaseOrder::whereIn('status', ['draft', 'sent'])->count(),
            ];
        });
    }

    public function arrivalsToday(): array
    {
        return Cache::remember('dashboard.arrivals', self::TTL, fn () =>
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

    public function orderTrends(string $period = 'day'): array
    {
        $cacheKey = "dashboard.order-trends.{$period}";

        return Cache::remember($cacheKey, self::TTL, fn () => $this->buildOrderTrends($period));
    }

    private function buildOrderTrends(string $period): array
    {
        $start = $period === 'month'
            ? now()->subMonths(11)->startOfMonth()
            : now()->subDays(29)->startOfDay();

        $rows = DB::table('purchase_order_items')
            ->join('purchase_orders', 'purchase_orders.id', '=', 'purchase_order_items.purchase_order_id')
            ->where('purchase_orders.created_at', '>=', $start)
            ->whereNot('purchase_orders.status', 'draft')
            ->select(
                'purchase_orders.id as purchase_order_id',
                'purchase_orders.created_at',
                'purchase_order_items.quantity_ordered'
            )
            ->get();

        $buckets = [];

        foreach ($rows as $row) {
            $key = $period === 'month'
                ? Carbon::parse($row->created_at)->format('Y-m')
                : Carbon::parse($row->created_at)->format('Y-m-d');

            if (! isset($buckets[$key])) {
                $buckets[$key] = ['units' => 0, 'orders' => []];
            }

            $buckets[$key]['units'] += (float) $row->quantity_ordered;
            $buckets[$key]['orders'][$row->purchase_order_id] = true;
        }

        $labels = [];
        $unitsOrdered = [];
        $orderCount = [];

        if ($period === 'month') {
            for ($i = 11; $i >= 0; $i--) {
                $date = now()->subMonths($i)->startOfMonth();
                $key = $date->format('Y-m');
                $labels[] = $date->format('M Y');
                $unitsOrdered[] = (int) round($buckets[$key]['units'] ?? 0);
                $orderCount[] = isset($buckets[$key]) ? count($buckets[$key]['orders']) : 0;
            }
        } else {
            for ($i = 29; $i >= 0; $i--) {
                $date = now()->subDays($i)->startOfDay();
                $key = $date->format('Y-m-d');
                $labels[] = $date->format('M j');
                $unitsOrdered[] = (int) round($buckets[$key]['units'] ?? 0);
                $orderCount[] = isset($buckets[$key]) ? count($buckets[$key]['orders']) : 0;
            }
        }

        return [
            'period' => $period,
            'labels' => $labels,
            'units_ordered' => $unitsOrdered,
            'order_count' => $orderCount,
            'totals' => [
                'units' => array_sum($unitsOrdered),
                'orders' => array_sum($orderCount),
            ],
        ];
    }
}