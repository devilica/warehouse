<?php

namespace Database\Seeders;

use App\Domains\Shared\Enums\MovementType;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\StockLevel;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use App\Models\WarehouseShelf;
use App\Models\WarehouseZone;
use App\Services\DashboardService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $locations = $this->seedWarehouseLocations();
        $this->seedMasterData();
        $this->seedStockLevels($locations);
        $this->seedInventoryTransactions($locations);
        $this->seedPurchaseOrders();
        $this->seedSupplierContacts();
        $this->seedGoodsReceipts($locations);
        $this->seedInventoryAdjustments($locations);
        $this->seedStockTransfers($locations);
        $this->seedInventoryCounts($locations);
        $this->seedAuditLogs();
        $this->seedNotifications();

        app(DashboardService::class)->clearCache();

        $this->command?->info('Demo data seeded successfully.');
    }

    private function seedWarehouseLocations(): Collection
    {
        if (WarehouseLocation::count() > 0) {
            return WarehouseLocation::all();
        }

        $locations = collect();

        foreach (Warehouse::all() as $warehouse) {
            for ($z = 1; $z <= 2; $z++) {
                $zone = WarehouseZone::create([
                    'warehouse_id' => $warehouse->id,
                    'name' => "Zone {$z}",
                    'code' => "Z{$z}",
                ]);

                for ($s = 1; $s <= 2; $s++) {
                    $shelf = WarehouseShelf::create([
                        'warehouse_zone_id' => $zone->id,
                        'name' => "Shelf {$s}",
                        'code' => "S{$s}",
                    ]);

                    $location = WarehouseLocation::create([
                        'warehouse_id' => $warehouse->id,
                        'warehouse_shelf_id' => $shelf->id,
                        'code' => "WH{$warehouse->id}-Z{$z}-S{$s}",
                        'name' => "Location {$z}-{$s}",
                        'is_active' => true,
                    ]);

                    $locations->push($location);
                }
            }
        }

        return $locations;
    }

    private function seedMasterData(): void
    {
        if (ProductCategory::count() <= 1) {
            ProductCategory::factory(3)->create();
        }

        if (Product::count() < 15) {
            Product::factory(15 - Product::count())->create();
        }

        if (Department::count() <= 1) {
            Department::create(['name' => 'Logistics', 'description' => 'Shipping and receiving']);
            Department::create(['name' => 'Procurement', 'description' => 'Purchasing team']);
        }

        if (Employee::count() === 0) {
            $department = Department::first();
            $users = User::limit(4)->get();

            foreach ($users as $index => $user) {
                Employee::create([
                    'user_id' => $user->id,
                    'department_id' => $department?->id,
                    'first_name' => explode(' ', $user->name)[0] ?? 'Employee',
                    'last_name' => explode(' ', $user->name)[1] ?? (string) ($index + 1),
                    'position' => fake()->randomElement(['Picker', 'Receiver', 'Supervisor', 'Clerk']),
                    'phone' => fake()->phoneNumber(),
                    'email' => $user->email,
                    'status' => 'active',
                ]);
            }
        }
    }

    private function seedStockLevels(Collection $locations): void
    {
        if ($locations->isEmpty()) {
            return;
        }

        $products = Product::all();
        $warehouses = Warehouse::all();

        foreach ($products as $index => $product) {
            if (StockLevel::where('product_id', $product->id)->exists()) {
                continue;
            }

            $location = $locations->random();
            $minStock = max((int) $product->min_stock, 5);
            $bucket = $index % 10;

            $quantity = match (true) {
                $bucket < 5 => $minStock + fake()->numberBetween(10, 50),
                $bucket < 8 => fake()->numberBetween(1, $minStock),
                default => 0,
            };

            StockLevel::create([
                'product_id' => $product->id,
                'warehouse_id' => $location->warehouse_id,
                'location_id' => $location->id,
                'quantity' => $quantity,
                'reserved_quantity' => $quantity > 0 ? fake()->numberBetween(0, min(3, (int) $quantity)) : 0,
            ]);

            if ($warehouses->count() > 1 && $bucket % 3 === 0 && $quantity > 0) {
                $extraLocation = $locations->where('warehouse_id', '!=', $location->warehouse_id)->first()
                    ?? $locations->random();

                StockLevel::create([
                    'product_id' => $product->id,
                    'warehouse_id' => $extraLocation->warehouse_id,
                    'location_id' => $extraLocation->id,
                    'quantity' => fake()->numberBetween(5, 25),
                    'reserved_quantity' => 0,
                ]);
            }
        }
    }

    private function seedInventoryTransactions(Collection $locations): void
    {
        if (DB::table('inventory_transactions')->count() > 0 || $locations->isEmpty()) {
            return;
        }

        $products = Product::all();
        $users = User::all();
        $types = array_map(fn (MovementType $t) => $t->value, MovementType::cases());

        for ($i = 0; $i < 40; $i++) {
            $product = $products->random();
            $location = $locations->random();
            $before = fake()->randomFloat(3, 0, 100);
            $change = fake()->randomFloat(3, -20, 30);
            $after = max(0, $before + $change);

            DB::table('inventory_transactions')->insert([
                'type' => fake()->randomElement($types),
                'product_id' => $product->id,
                'warehouse_id' => $location->warehouse_id,
                'location_id' => $location->id,
                'quantity_change' => $change,
                'quantity_before' => $before,
                'quantity_after' => $after,
                'reference_type' => null,
                'reference_id' => null,
                'user_id' => $users->random()->id,
                'metadata' => json_encode(['seed' => true]),
                'created_at' => now()->subDays(fake()->numberBetween(0, 7))->subHours(fake()->numberBetween(0, 23)),
            ]);
        }
    }

    private function seedPurchaseOrders(): void
    {
        if (DB::table('purchase_orders')->count() > 0) {
            return;
        }

        $suppliers = Supplier::all();
        $products = Product::all();
        $users = User::all();

        if ($suppliers->isEmpty() || $products->isEmpty() || $users->isEmpty()) {
            return;
        }

        $statuses = [
            ['status' => 'draft', 'date' => now()->addDays(5)],
            ['status' => 'draft', 'date' => now()->addDays(7)],
            ['status' => 'draft', 'date' => now()->addDays(10)],
            ['status' => 'sent', 'date' => now()->addDays(3)],
            ['status' => 'sent', 'date' => today()],
            ['status' => 'received', 'date' => now()->subDays(2)],
            ['status' => 'closed', 'date' => now()->subDays(5)],
            ['status' => 'closed', 'date' => now()->subDays(10)],
        ];

        foreach ($statuses as $index => $config) {
            $poId = DB::table('purchase_orders')->insertGetId([
                'number' => 'PO-'.str_pad((string) ($index + 1), 6, '0', STR_PAD_LEFT),
                'supplier_id' => $suppliers->random()->id,
                'status' => $config['status'],
                'expected_delivery_date' => $config['date']->toDateString(),
                'notes' => fake()->optional()->sentence(),
                'created_by' => $users->random()->id,
                'sent_at' => in_array($config['status'], ['sent', 'received', 'closed'], true) ? now()->subDays(3) : null,
                'closed_at' => $config['status'] === 'closed' ? now()->subDay() : null,
                'created_at' => now()->subDays(14 - $index),
                'updated_at' => now(),
            ]);

            foreach ($products->random(min(4, $products->count())) as $product) {
                DB::table('purchase_order_items')->insert([
                    'purchase_order_id' => $poId,
                    'product_id' => $product->id,
                    'quantity_ordered' => fake()->numberBetween(5, 50),
                    'quantity_received' => in_array($config['status'], ['received', 'closed'], true)
                        ? fake()->numberBetween(5, 50)
                        : 0,
                    'unit_price' => $product->purchase_price ?? fake()->randomFloat(2, 5, 100),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        for ($i = 1; $i <= 12; $i++) {
            $daysAgo = fake()->numberBetween(15, 30);
            $status = fake()->randomElement(['sent', 'received', 'closed']);
            $createdAt = now()->subDays($daysAgo);

            $poId = DB::table('purchase_orders')->insertGetId([
                'number' => 'PO-'.str_pad((string) (8 + $i), 6, '0', STR_PAD_LEFT),
                'supplier_id' => $suppliers->random()->id,
                'status' => $status,
                'expected_delivery_date' => $createdAt->copy()->addDays(fake()->numberBetween(3, 10))->toDateString(),
                'notes' => fake()->optional()->sentence(),
                'created_by' => $users->random()->id,
                'sent_at' => $createdAt->copy()->addHours(2),
                'closed_at' => $status === 'closed' ? $createdAt->copy()->addDays(2) : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            foreach ($products->random(fake()->numberBetween(2, min(5, $products->count()))) as $product) {
                DB::table('purchase_order_items')->insert([
                    'purchase_order_id' => $poId,
                    'product_id' => $product->id,
                    'quantity_ordered' => fake()->numberBetween(10, 80),
                    'quantity_received' => in_array($status, ['received', 'closed'], true)
                        ? fake()->numberBetween(10, 80)
                        : 0,
                    'unit_price' => $product->purchase_price ?? fake()->randomFloat(2, 5, 100),
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        }
    }

    private function seedSupplierContacts(): void
    {
        if (DB::table('supplier_contacts')->count() > 0) {
            return;
        }

        foreach (Supplier::all() as $supplier) {
            DB::table('supplier_contacts')->insert([
                'supplier_id' => $supplier->id,
                'name' => fake()->name(),
                'email' => fake()->safeEmail(),
                'phone' => fake()->phoneNumber(),
                'position' => fake()->randomElement(['Sales Rep', 'Account Manager', 'Support']),
                'is_primary' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedGoodsReceipts(Collection $locations): void
    {
        if (DB::table('goods_receipts')->count() > 0 || $locations->isEmpty()) {
            return;
        }

        $orders = DB::table('purchase_orders')->whereIn('status', ['received', 'closed'])->limit(2)->get();
        $users = User::all();

        foreach ($orders as $index => $order) {
            $receiptId = DB::table('goods_receipts')->insertGetId([
                'purchase_order_id' => $order->id,
                'number' => 'GR-'.str_pad((string) ($index + 1), 6, '0', STR_PAD_LEFT),
                'status' => 'completed',
                'received_by' => $users->random()->id,
                'received_at' => now()->subDays($index + 1),
                'notes' => 'Demo goods receipt',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $poItems = DB::table('purchase_order_items')->where('purchase_order_id', $order->id)->get();
            $location = $locations->random();

            foreach ($poItems as $item) {
                DB::table('goods_receipt_items')->insert([
                    'goods_receipt_id' => $receiptId,
                    'purchase_order_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'quantity_received' => $item->quantity_ordered,
                    'quantity_damaged' => 0,
                    'quantity_missing' => 0,
                    'warehouse_id' => $location->warehouse_id,
                    'location_id' => $location->id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function seedInventoryAdjustments(Collection $locations): void
    {
        if (DB::table('inventory_adjustments')->count() > 0 || $locations->isEmpty()) {
            return;
        }

        $warehouse = Warehouse::first();
        $users = User::all();
        $products = Product::limit(4)->get();

        for ($i = 1; $i <= 2; $i++) {
            $adjustmentId = DB::table('inventory_adjustments')->insertGetId([
                'number' => 'ADJ-'.str_pad((string) $i, 6, '0', STR_PAD_LEFT),
                'warehouse_id' => $warehouse->id,
                'status' => $i === 1 ? 'approved' : 'draft',
                'reason' => fake()->randomElement(['cycle_count', 'damage', 'manual_correction']),
                'notes' => 'Demo adjustment',
                'created_by' => $users->random()->id,
                'approved_by' => $i === 1 ? $users->random()->id : null,
                'approved_at' => $i === 1 ? now()->subDay() : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($products->random(min(2, $products->count())) as $product) {
                $location = $locations->where('warehouse_id', $warehouse->id)->first() ?? $locations->first();
                $before = fake()->randomFloat(3, 5, 50);
                $change = fake()->randomFloat(3, -5, 5);

                DB::table('inventory_adjustment_items')->insert([
                    'inventory_adjustment_id' => $adjustmentId,
                    'product_id' => $product->id,
                    'location_id' => $location->id,
                    'quantity_before' => $before,
                    'quantity_after' => max(0, $before + $change),
                    'quantity_change' => $change,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function seedStockTransfers(Collection $locations): void
    {
        if (DB::table('stock_transfers')->count() > 0 || $locations->isEmpty()) {
            return;
        }

        $warehouses = Warehouse::all();
        if ($warehouses->count() < 2) {
            return;
        }

        $users = User::all();
        $products = Product::limit(4)->get();
        $from = $warehouses->first();
        $to = $warehouses->last();

        for ($i = 1; $i <= 2; $i++) {
            $transferId = DB::table('stock_transfers')->insertGetId([
                'number' => 'TR-'.str_pad((string) $i, 6, '0', STR_PAD_LEFT),
                'from_warehouse_id' => $from->id,
                'to_warehouse_id' => $to->id,
                'status' => $i === 1 ? 'completed' : 'draft',
                'notes' => 'Demo stock transfer',
                'created_by' => $users->random()->id,
                'approved_by' => $i === 1 ? $users->random()->id : null,
                'shipped_at' => $i === 1 ? now()->subDays(2) : null,
                'received_at' => $i === 1 ? now()->subDay() : null,
                'completed_at' => $i === 1 ? now()->subDay() : null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $fromLocation = $locations->where('warehouse_id', $from->id)->first() ?? $locations->first();
            $toLocation = $locations->where('warehouse_id', $to->id)->first() ?? $locations->last();

            foreach ($products->random(min(2, $products->count())) as $product) {
                $qty = fake()->numberBetween(5, 20);

                DB::table('stock_transfer_items')->insert([
                    'stock_transfer_id' => $transferId,
                    'product_id' => $product->id,
                    'from_location_id' => $fromLocation->id,
                    'to_location_id' => $toLocation->id,
                    'quantity_requested' => $qty,
                    'quantity_shipped' => $i === 1 ? $qty : 0,
                    'quantity_received' => $i === 1 ? $qty : 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function seedInventoryCounts(Collection $locations): void
    {
        if (DB::table('inventory_counts')->count() > 0 || $locations->isEmpty()) {
            return;
        }

        $warehouse = Warehouse::first();
        $users = User::all();
        $products = Product::limit(5)->get();
        $location = $locations->where('warehouse_id', $warehouse->id)->first() ?? $locations->first();

        $countId = DB::table('inventory_counts')->insertGetId([
            'number' => 'CNT-000001',
            'warehouse_id' => $warehouse->id,
            'status' => 'scheduled',
            'type' => 'full',
            'scheduled_at' => now()->addDay(),
            'started_at' => null,
            'completed_at' => null,
            'created_by' => $users->random()->id,
            'notes' => 'Demo inventory count',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ($products as $product) {
            $expected = fake()->randomFloat(3, 10, 100);

            DB::table('inventory_count_items')->insert([
                'inventory_count_id' => $countId,
                'product_id' => $product->id,
                'location_id' => $location->id,
                'expected_quantity' => $expected,
                'counted_quantity' => null,
                'variance' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedAuditLogs(): void
    {
        if (DB::table('audit_logs')->count() > 0) {
            return;
        }

        $user = User::where('email', 'admin@wms.test')->first() ?? User::first();

        if (! $user) {
            return;
        }

        $actions = ['login', 'view', 'create', 'update', 'export'];

        for ($i = 0; $i < 10; $i++) {
            DB::table('audit_logs')->insert([
                'user_id' => $user->id,
                'action' => fake()->randomElement($actions),
                'ip' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
                'auditable_type' => Product::class,
                'auditable_id' => Product::inRandomOrder()->value('id'),
                'old_values' => json_encode(['status' => 'draft']),
                'new_values' => json_encode(['status' => 'active']),
                'created_at' => now()->subDays(fake()->numberBetween(0, 14)),
            ]);
        }
    }

    private function seedNotifications(): void
    {
        if (DB::table('notifications')->count() > 0) {
            return;
        }

        $user = User::where('email', 'admin@wms.test')->first() ?? User::first();

        if (! $user) {
            return;
        }

        $messages = [
            'Low stock alert: 3 products below minimum',
            'Purchase order PO-000004 awaiting approval',
            'Goods receipt GR-000001 completed',
            'Inventory count scheduled for tomorrow',
            'New transfer TR-000001 shipped',
        ];

        foreach ($messages as $message) {
            DB::table('notifications')->insert([
                'id' => Str::uuid()->toString(),
                'type' => 'App\\Notifications\\DemoNotification',
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'data' => json_encode(['message' => $message]),
                'read_at' => null,
                'created_at' => now()->subHours(fake()->numberBetween(1, 48)),
                'updated_at' => now(),
            ]);
        }
    }
}
