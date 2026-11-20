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
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

class DemoDataSeeder extends Seeder
{
    private int $skuCursor = 0;

    /** @var array<string, int> */
    private array $numberCursors = [];

    public function run(): void
    {
        $this->seedWarehouses();
        $this->seedDepartments();
        $this->seedSuppliers();
        $this->seedCategories();
        $this->seedEmployees();
        $locations = $this->seedWarehouseLocations();
        $this->seedProducts();
        $this->seedSupplierContacts();
        $this->seedEmployeeWarehouses();
        $this->seedStockLevels($locations);
        $this->seedProductDetails($locations);
        $this->seedInventoryTransactions($locations);
        $this->seedPurchaseOrders();
        $this->seedGoodsReceipts($locations);
        $this->seedInventoryAdjustments($locations);
        $this->seedStockTransfers($locations);
        $this->seedInventoryCounts($locations);
        $this->seedAuditLogs();
        $this->seedNotifications();

        app(DashboardService::class)->clearCache();

        $this->command?->info('Demo data seeded successfully.');
    }

    private function seedWarehouses(): void
    {
        $missing = 6 - Warehouse::count();

        if ($missing > 0) {
            Warehouse::factory($missing)->create();
        }
    }

    private function seedDepartments(): void
    {
        $departments = [
            'Operations' => 'Warehouse operations',
            'Logistics' => 'Shipping and receiving',
            'Procurement' => 'Purchasing team',
            'Quality' => 'Quality control and inspections',
        ];

        foreach ($departments as $name => $description) {
            if (Department::count() >= 4) {
                break;
            }

            Department::firstOrCreate(
                ['name' => $name],
                ['description' => $description]
            );
        }
    }

    private function seedSuppliers(): void
    {
        $missing = 12 - Supplier::count();

        if ($missing > 0) {
            Supplier::factory($missing)->create();
        }
    }

    private function seedCategories(): void
    {
        $roots = ['Packaging', 'Safety', 'Tools', 'Spare Parts', 'Chemicals', 'Electronics'];
        $children = [
            'Boxes' => 'Packaging',
            'Tape' => 'Packaging',
            'Gloves' => 'Safety',
            'Fasteners' => 'Spare Parts',
        ];

        foreach ($roots as $name) {
            if (ProductCategory::count() >= 10) {
                break;
            }

            $this->firstOrCreateCategory($name);
        }

        foreach ($children as $name => $parentName) {
            if (ProductCategory::count() >= 10) {
                break;
            }

            $parent = ProductCategory::where('name', $parentName)->first();
            $this->firstOrCreateCategory($name, $parent?->id);
        }

        $extra = 1;

        while (ProductCategory::count() < 10) {
            $parent = ProductCategory::query()->whereNull('parent_id')->inRandomOrder()->first();
            $this->firstOrCreateCategory('General '.str_pad((string) $extra, 2, '0', STR_PAD_LEFT), $parent?->id);
            $extra++;
        }
    }

    private function firstOrCreateCategory(string $name, ?int $parentId = null): ProductCategory
    {
        $existing = ProductCategory::where('name', $name)->first();

        if ($existing) {
            return $existing;
        }

        return ProductCategory::create([
            'name' => $name,
            'slug' => $this->uniqueSlug($name),
            'parent_id' => $parentId,
        ]);
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'category';
        $slug = $base;
        $suffix = 2;

        while (ProductCategory::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    private function seedEmployees(): void
    {
        $missing = 12 - Employee::count();

        if ($missing <= 0) {
            return;
        }

        $departmentIds = Department::pluck('id');
        $roles = ['warehouse-employee', 'warehouse-manager', 'purchasing-manager', 'viewer'];
        $positions = ['Picker', 'Receiver', 'Supervisor', 'Clerk', 'Forklift Operator', 'Inventory Analyst'];

        for ($i = 0; $i < $missing; $i++) {
            $user = User::factory()->create([
                'email' => 'staff.'.Str::lower(Str::random(8)).'@wms.test',
                'locale' => 'en',
            ]);

            $role = $roles[$i % count($roles)];

            if (Role::where('name', $role)->where('guard_name', 'web')->exists()) {
                $user->assignRole($role);
            }

            [$first, $last] = array_pad(preg_split('/\s+/', $user->name, 2) ?: [], 2, null);

            $employee = new Employee;
            $employee->forceFill([
                'user_id' => $user->id,
                'department_id' => $departmentIds->isEmpty() ? null : $departmentIds->random(),
                'first_name' => $first ?: 'Employee',
                'last_name' => $last ?: (string) ($i + 1),
                'position' => $positions[$i % count($positions)],
                'phone' => fake()->phoneNumber(),
                'email' => $user->email,
                'status' => 'active',
            ])->save();
        }
    }

    private function seedWarehouseLocations(): Collection
    {
        foreach (Warehouse::all() as $warehouse) {
            $zones = WarehouseZone::where('warehouse_id', $warehouse->id)->orderBy('id')->get();
            $zoneNumber = $zones->count();

            while ($zones->count() < 3) {
                $zoneNumber++;
                $zones->push(WarehouseZone::create([
                    'warehouse_id' => $warehouse->id,
                    'name' => "Zone {$zoneNumber}",
                    'code' => "Z{$zoneNumber}",
                ]));
            }

            foreach ($zones as $zone) {
                $shelfCount = WarehouseShelf::where('warehouse_zone_id', $zone->id)->count();

                for ($shelfNumber = $shelfCount + 1; $shelfNumber <= 2; $shelfNumber++) {
                    $shelf = WarehouseShelf::create([
                        'warehouse_zone_id' => $zone->id,
                        'name' => "Shelf {$shelfNumber}",
                        'code' => "S{$shelfNumber}",
                    ]);

                    WarehouseLocation::create([
                        'warehouse_id' => $warehouse->id,
                        'warehouse_shelf_id' => $shelf->id,
                        'code' => "WH{$warehouse->id}-Z{$zone->id}-S{$shelf->id}",
                        'name' => "{$zone->name} / {$shelf->name}",
                        'is_active' => true,
                    ]);
                }
            }
        }

        return WarehouseLocation::all();
    }

    private function seedProducts(): void
    {
        $missing = 60 - Product::count();

        if ($missing <= 0) {
            return;
        }

        $categories = ProductCategory::all();
        $suppliers = Supplier::all();

        if ($categories->isEmpty() || $suppliers->isEmpty()) {
            return;
        }

        for ($i = 0; $i < $missing; $i++) {
            $minStock = fake()->numberBetween(5, 25);

            Product::factory()->create([
                'name' => $this->catalogName($i),
                'sku' => $this->nextSku(),
                'barcode' => $this->uniqueBarcode(),
                'description' => fake()->sentence(8),
                'category_id' => $categories->random()->id,
                'supplier_id' => $suppliers->random()->id,
                'purchase_price' => fake()->randomFloat(2, 2, 120),
                'selling_price' => fake()->randomFloat(2, 8, 240),
                'tax_rate' => fake()->randomElement([0, 10, 20]),
                'unit' => fake()->randomElement(['pcs', 'box', 'kg', 'm']),
                'min_stock' => $minStock,
                'max_stock' => $minStock + fake()->numberBetween(40, 150),
            ]);
        }
    }

    private function seedSupplierContacts(): void
    {
        foreach (Supplier::all() as $supplier) {
            $existing = DB::table('supplier_contacts')->where('supplier_id', $supplier->id)->count();
            $hasPrimary = DB::table('supplier_contacts')
                ->where('supplier_id', $supplier->id)
                ->where('is_primary', true)
                ->exists();

            for ($i = $existing; $i < 2; $i++) {
                DB::table('supplier_contacts')->insert([
                    'supplier_id' => $supplier->id,
                    'name' => fake()->name(),
                    'email' => fake()->safeEmail(),
                    'phone' => fake()->phoneNumber(),
                    'position' => fake()->randomElement(['Sales Rep', 'Account Manager', 'Support', 'Logistics Contact']),
                    'is_primary' => ! $hasPrimary && $i === $existing,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $hasPrimary = true;
            }
        }
    }

    private function seedEmployeeWarehouses(): void
    {
        $warehouses = Warehouse::all();

        if ($warehouses->isEmpty()) {
            return;
        }

        foreach (Employee::all() as $index => $employee) {
            $wanted = $index % 2 === 0 ? 2 : 1;
            $assigned = DB::table('employee_warehouse')->where('employee_id', $employee->id)->pluck('warehouse_id');
            $choices = $warehouses->pluck('id')->diff($assigned)->values();

            foreach ($choices->take(max(0, $wanted - $assigned->count())) as $warehouseId) {
                DB::table('employee_warehouse')->insert([
                    'employee_id' => $employee->id,
                    'warehouse_id' => $warehouseId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    private function seedStockLevels(Collection $locations): void
    {
        if ($locations->isEmpty()) {
            return;
        }

        foreach (Product::all()->values() as $index => $product) {
            $existing = StockLevel::where('product_id', $product->id)->get();

            if ($existing->isEmpty()) {
                $location = $locations->random();
                $existing->push($this->createStockLevel($product, $location, $index));
            }

            if ($existing->count() !== 1 || $index % 3 !== 0) {
                continue;
            }

            $currentWarehouse = $existing->first()->warehouse_id;
            $extra = $locations->first(function ($location) use ($existing, $currentWarehouse) {
                if ((int) $location->warehouse_id === (int) $currentWarehouse) {
                    return false;
                }

                return ! $existing->contains(fn ($row) => (int) $row->location_id === (int) $location->id
                    && (int) $row->warehouse_id === (int) $location->warehouse_id);
            });

            if ($extra) {
                $this->createStockLevel($product, $extra, $index, true);
            }
        }
    }

    private function createStockLevel(Product $product, WarehouseLocation $location, int $index, bool $secondary = false): StockLevel
    {
        $minStock = max((int) $product->min_stock, 5);
        $bucket = $index % 10;

        $quantity = $secondary
            ? fake()->numberBetween(8, 40)
            : match (true) {
                $bucket < 7 => $minStock + fake()->numberBetween(10, 80),
                $bucket < 9 => fake()->numberBetween(1, max(1, $minStock - 1)),
                default => 0,
            };

        return StockLevel::create([
            'product_id' => $product->id,
            'warehouse_id' => $location->warehouse_id,
            'location_id' => $location->id,
            'quantity' => $quantity,
            'reserved_quantity' => $quantity > 0 ? fake()->numberBetween(0, min(5, (int) $quantity)) : 0,
        ]);
    }

    private function seedProductDetails(Collection $locations): void
    {
        $products = Product::all();

        if ($products->isEmpty()) {
            return;
        }

        foreach ($products as $product) {
            $hasBarcode = DB::table('product_barcodes')->where('product_id', $product->id)->exists();

            if ($hasBarcode) {
                continue;
            }

            $code = $product->barcode && ! DB::table('product_barcodes')->where('barcode', $product->barcode)->exists()
                ? $product->barcode
                : $this->uniqueBarcode();

            DB::table('product_barcodes')->insert([
                'product_id' => $product->id,
                'barcode' => $code,
                'type' => 'primary',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $batchProducts = $products->filter(function ($product) {
            return ! DB::table('product_batches')->where('product_id', $product->id)->exists();
        })->take(20);

        foreach ($batchProducts->values() as $index => $product) {
            $product->update(['tracks_batch' => true]);

            DB::table('product_batches')->insert([
                'product_id' => $product->id,
                'lot_number' => 'LOT-'.str_pad((string) $product->id, 4, '0', STR_PAD_LEFT),
                'expiration_date' => $index < 8
                    ? now()->addDays(fake()->numberBetween(5, 25))->toDateString()
                    : now()->addMonths(fake()->numberBetween(4, 14))->toDateString(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $serialProducts = $products->filter(function ($product) {
            return ! DB::table('product_serial_numbers')->where('product_id', $product->id)->exists();
        })->take(10);

        $locationId = $locations->first()?->id;

        foreach ($serialProducts as $product) {
            $product->update(['tracks_serial' => true]);

            for ($i = 0; $i < 4; $i++) {
                DB::table('product_serial_numbers')->insert([
                    'product_id' => $product->id,
                    'serial_number' => $this->uniqueSerial(),
                    'warehouse_location_id' => $locationId,
                    'status' => $i === 0 ? 'reserved' : 'available',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $this->seedFileRows('product_images', $products, 12, function ($product) {
            return [
                'product_id' => $product->id,
                'path' => "products/demo/{$product->id}.jpg",
                'alt_text' => $product->name,
                'is_primary' => true,
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });

        $this->seedFileRows('product_documents', $products, 12, function ($product) {
            return [
                'product_id' => $product->id,
                'name' => $product->sku.' specification.pdf',
                'path' => "products/documents/{$product->id}.pdf",
                'mime_type' => 'application/pdf',
                'size' => fake()->numberBetween(40_000, 800_000),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });

        $mediaCount = DB::table('media_files')->count();

        foreach ($products->take(max(0, 12 - $mediaCount)) as $product) {
            DB::table('media_files')->insert([
                'mediable_type' => Product::class,
                'mediable_id' => $product->id,
                'file_name' => Str::slug($product->sku).'.jpg',
                'path' => "media/products/{$product->id}.jpg",
                'mime_type' => 'image/jpeg',
                'size' => fake()->numberBetween(20_000, 400_000),
                'disk' => 'local',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    private function seedFileRows(string $table, Collection $products, int $target, callable $row): void
    {
        $have = DB::table($table)->pluck('product_id');
        $needed = $target - DB::table($table)->count();

        if ($needed <= 0) {
            return;
        }

        foreach ($products->reject(fn ($product) => $have->contains($product->id))->take($needed) as $product) {
            DB::table($table)->insert($row($product));
        }
    }

    private function seedInventoryTransactions(Collection $locations): void
    {
        $missing = 150 - DB::table('inventory_transactions')->count();

        if ($missing <= 0 || $locations->isEmpty() || Product::count() === 0 || User::count() === 0) {
            return;
        }

        $products = Product::all();
        $users = User::all();
        $types = array_map(fn (MovementType $type) => $type->value, MovementType::cases());

        for ($i = 0; $i < $missing; $i++) {
            $product = $products->random();
            $location = $locations->random();
            $before = fake()->randomFloat(3, 0, 120);
            $change = fake()->randomFloat(3, -20, 40);
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
                'created_at' => now()->subDays(fake()->numberBetween(0, 29))->subHours(fake()->numberBetween(0, 23)),
            ]);
        }
    }

    private function seedPurchaseOrders(): void
    {
        $missing = 48 - DB::table('purchase_orders')->count();
        $suppliers = Supplier::all();
        $products = Product::all();
        $users = User::all();

        if ($missing <= 0 || $suppliers->isEmpty() || $products->isEmpty() || $users->isEmpty()) {
            $this->ensureArrivalsToday();

            return;
        }

        $statuses = ['draft', 'sent', 'sent', 'partially_received', 'received', 'closed'];

        for ($i = 0; $i < $missing; $i++) {
            $status = $i < 4 ? 'sent' : $statuses[$i % count($statuses)];
            $createdAt = now()->subDays(fake()->numberBetween(0, 89))->setTime(fake()->numberBetween(7, 18), fake()->numberBetween(0, 59));
            $expected = $i < 4
                ? today()
                : ($status === 'draft' || $status === 'sent'
                    ? now()->addDays(fake()->numberBetween(0, 14))
                    : $createdAt->copy()->addDays(fake()->numberBetween(2, 12)));

            $this->insertPurchaseOrder($status, $createdAt, $expected, $suppliers, $products, $users);
        }

        $this->ensureArrivalsToday();
    }

    private function ensureArrivalsToday(): void
    {
        $today = DB::table('purchase_orders')->whereDate('expected_delivery_date', today())->count();
        $needed = 3 - $today;

        if ($needed <= 0) {
            return;
        }

        $candidates = DB::table('purchase_orders')
            ->where('status', 'sent')
            ->where(function ($query) {
                $query->whereNull('expected_delivery_date')
                    ->orWhereDate('expected_delivery_date', '!=', today());
            })
            ->limit($needed)
            ->pluck('id');

        if ($candidates->isNotEmpty()) {
            DB::table('purchase_orders')->whereIn('id', $candidates)->update([
                'expected_delivery_date' => today()->toDateString(),
                'updated_at' => now(),
            ]);
        }
    }

    private function insertPurchaseOrder(string $status, $createdAt, $expected, Collection $suppliers, Collection $products, Collection $users): void
    {
        $poId = DB::table('purchase_orders')->insertGetId([
            'number' => $this->nextDocumentNumber('purchase_orders', 'PO-'),
            'supplier_id' => $suppliers->random()->id,
            'status' => $status,
            'expected_delivery_date' => $expected->toDateString(),
            'notes' => fake()->optional()->sentence(),
            'created_by' => $users->random()->id,
            'sent_at' => $status === 'draft' ? null : $createdAt->copy()->addHours(2),
            'closed_at' => $status === 'closed' ? $createdAt->copy()->addDays(3) : null,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);

        $lineCount = fake()->numberBetween(3, min(6, $products->count()));

        foreach ($products->random($lineCount) as $product) {
            $ordered = fake()->numberBetween(5, 60);
            $received = match ($status) {
                'received', 'closed' => $ordered,
                'partially_received' => max(1, (int) floor($ordered / 2)),
                default => 0,
            };

            DB::table('purchase_order_items')->insert([
                'purchase_order_id' => $poId,
                'product_id' => $product->id,
                'quantity_ordered' => $ordered,
                'quantity_received' => $received,
                'unit_price' => $product->purchase_price ?? fake()->randomFloat(2, 5, 100),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);
        }
    }

    private function seedGoodsReceipts(Collection $locations): void
    {
        if ($locations->isEmpty() || User::count() === 0) {
            return;
        }

        $orders = DB::table('purchase_orders')
            ->whereIn('status', ['received', 'closed'])
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('goods_receipts')
                    ->whereColumn('goods_receipts.purchase_order_id', 'purchase_orders.id');
            })
            ->get();

        $users = User::all();

        foreach ($orders as $order) {
            $receivedAt = $order->created_at ? Carbon::parse($order->created_at)->addDays(2) : now()->subDay();

            $receiptId = DB::table('goods_receipts')->insertGetId([
                'purchase_order_id' => $order->id,
                'number' => $this->nextDocumentNumber('goods_receipts', 'GR-'),
                'status' => 'confirmed',
                'received_by' => $users->random()->id,
                'received_at' => $receivedAt,
                'notes' => 'Received against '.$order->number,
                'created_at' => $receivedAt,
                'updated_at' => $receivedAt,
            ]);

            $location = $locations->random();
            $items = DB::table('purchase_order_items')->where('purchase_order_id', $order->id)->get();

            foreach ($items as $item) {
                DB::table('goods_receipt_items')->insert([
                    'goods_receipt_id' => $receiptId,
                    'purchase_order_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'quantity_received' => $item->quantity_received > 0 ? $item->quantity_received : $item->quantity_ordered,
                    'quantity_damaged' => 0,
                    'quantity_missing' => 0,
                    'warehouse_id' => $location->warehouse_id,
                    'location_id' => $location->id,
                    'created_at' => $receivedAt,
                    'updated_at' => $receivedAt,
                ]);
            }
        }
    }

    private function seedInventoryAdjustments(Collection $locations): void
    {
        $missing = 10 - DB::table('inventory_adjustments')->count();

        if ($missing <= 0 || $locations->isEmpty() || Product::count() === 0 || User::count() === 0) {
            return;
        }

        $users = User::all();
        $products = Product::all();
        $statuses = ['draft', 'pending_approval', 'approved', 'rejected'];
        $reasons = ['cycle_count', 'damage', 'manual_correction'];

        for ($i = 0; $i < $missing; $i++) {
            $location = $locations->random();
            $status = $statuses[$i % count($statuses)];
            $createdAt = now()->subDays(fake()->numberBetween(0, 21));

            $adjustmentId = DB::table('inventory_adjustments')->insertGetId([
                'number' => $this->nextDocumentNumber('inventory_adjustments', 'ADJ-'),
                'warehouse_id' => $location->warehouse_id,
                'status' => $status,
                'reason' => $reasons[$i % count($reasons)],
                'notes' => fake()->sentence(),
                'created_by' => $users->random()->id,
                'approved_by' => $status === 'approved' ? $users->random()->id : null,
                'approved_at' => $status === 'approved' ? $createdAt->copy()->addDay() : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            foreach ($products->random(min(3, $products->count())) as $product) {
                $before = fake()->randomFloat(3, 5, 80);
                $change = fake()->randomFloat(3, -8, 8);

                DB::table('inventory_adjustment_items')->insert([
                    'inventory_adjustment_id' => $adjustmentId,
                    'product_id' => $product->id,
                    'location_id' => $location->id,
                    'quantity_before' => $before,
                    'quantity_after' => max(0, $before + $change),
                    'quantity_change' => $change,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        }
    }

    private function seedStockTransfers(Collection $locations): void
    {
        $missing = 8 - DB::table('stock_transfers')->count();
        $warehouses = Warehouse::all();

        if ($missing <= 0 || $warehouses->count() < 2 || $locations->isEmpty() || Product::count() === 0 || User::count() === 0) {
            return;
        }

        $users = User::all();
        $products = Product::all();
        $statuses = ['draft', 'approved', 'shipped', 'received', 'completed'];

        for ($i = 0; $i < $missing; $i++) {
            $from = $warehouses->random();
            $to = $warehouses->where('id', '!=', $from->id)->random();
            $status = $statuses[$i % count($statuses)];
            $createdAt = now()->subDays(fake()->numberBetween(1, 20));
            $moved = in_array($status, ['shipped', 'received', 'completed'], true);

            $transferId = DB::table('stock_transfers')->insertGetId([
                'number' => $this->nextDocumentNumber('stock_transfers', 'TR-'),
                'from_warehouse_id' => $from->id,
                'to_warehouse_id' => $to->id,
                'status' => $status,
                'notes' => fake()->sentence(),
                'created_by' => $users->random()->id,
                'approved_by' => $status === 'draft' ? null : $users->random()->id,
                'shipped_at' => $moved ? $createdAt->copy()->addDay() : null,
                'received_at' => in_array($status, ['received', 'completed'], true) ? $createdAt->copy()->addDays(2) : null,
                'completed_at' => $status === 'completed' ? $createdAt->copy()->addDays(3) : null,
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            $fromLocation = $locations->firstWhere('warehouse_id', $from->id) ?? $locations->first();
            $toLocation = $locations->firstWhere('warehouse_id', $to->id) ?? $locations->last();

            foreach ($products->random(min(3, $products->count())) as $product) {
                $qty = fake()->numberBetween(4, 24);

                DB::table('stock_transfer_items')->insert([
                    'stock_transfer_id' => $transferId,
                    'product_id' => $product->id,
                    'from_location_id' => $fromLocation->id,
                    'to_location_id' => $toLocation->id,
                    'quantity_requested' => $qty,
                    'quantity_shipped' => $moved ? $qty : 0,
                    'quantity_received' => in_array($status, ['received', 'completed'], true) ? $qty : 0,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        }
    }

    private function seedInventoryCounts(Collection $locations): void
    {
        $missing = 6 - DB::table('inventory_counts')->count();

        if ($missing <= 0 || $locations->isEmpty() || Product::count() === 0 || User::count() === 0) {
            return;
        }

        $users = User::all();
        $products = Product::all();
        $statuses = ['scheduled', 'in_progress', 'completed'];

        for ($i = 0; $i < $missing; $i++) {
            $location = $locations->random();
            $status = $statuses[$i % count($statuses)];
            $createdAt = now()->subDays(fake()->numberBetween(0, 14));

            $countId = DB::table('inventory_counts')->insertGetId([
                'number' => $this->nextDocumentNumber('inventory_counts', 'CNT-'),
                'warehouse_id' => $location->warehouse_id,
                'status' => $status,
                'type' => $i % 2 === 0 ? 'full' : 'partial',
                'scheduled_at' => $status === 'scheduled' ? now()->addDays($i + 1) : $createdAt,
                'started_at' => $status === 'scheduled' ? null : $createdAt->copy()->addHours(2),
                'completed_at' => $status === 'completed' ? $createdAt->copy()->addHours(6) : null,
                'created_by' => $users->random()->id,
                'notes' => fake()->sentence(),
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            foreach ($products->random(min(5, $products->count())) as $product) {
                $expected = fake()->randomFloat(3, 8, 90);
                $counted = $status === 'scheduled' ? null : $expected + fake()->randomFloat(3, -3, 3);

                DB::table('inventory_count_items')->insert([
                    'inventory_count_id' => $countId,
                    'product_id' => $product->id,
                    'location_id' => $location->id,
                    'expected_quantity' => $expected,
                    'counted_quantity' => $counted,
                    'variance' => $counted === null ? null : $counted - $expected,
                    'created_at' => $createdAt,
                    'updated_at' => $createdAt,
                ]);
            }
        }
    }

    private function seedAuditLogs(): void
    {
        $missing = 40 - DB::table('audit_logs')->count();
        $userIds = User::pluck('id');

        if ($missing <= 0 || $userIds->isEmpty()) {
            return;
        }

        $actions = ['login', 'view', 'create', 'update', 'export'];
        $productIds = Product::pluck('id');

        for ($i = 0; $i < $missing; $i++) {
            DB::table('audit_logs')->insert([
                'user_id' => $userIds->random(),
                'action' => $actions[$i % count($actions)],
                'ip' => fake()->ipv4(),
                'user_agent' => fake()->userAgent(),
                'auditable_type' => Product::class,
                'auditable_id' => $productIds->isEmpty() ? null : $productIds->random(),
                'old_values' => json_encode(['status' => 'draft']),
                'new_values' => json_encode(['status' => 'active']),
                'created_at' => now()->subDays(fake()->numberBetween(0, 21))->subHours(fake()->numberBetween(0, 12)),
            ]);
        }
    }

    private function seedNotifications(): void
    {
        $user = User::where('email', 'admin@wms.test')->first() ?? User::first();

        if (! $user) {
            return;
        }

        $existing = DB::table('notifications')
            ->where('notifiable_type', User::class)
            ->where('notifiable_id', $user->id)
            ->count();
        $missing = 12 - $existing;

        if ($missing <= 0) {
            return;
        }

        $messages = [
            'Low stock alert: several products are below minimum',
            'Purchase orders are waiting to be sent',
            'Goods receipt confirmed and stock updated',
            'Inventory count is scheduled this week',
            'Stock transfer shipped between warehouses',
            'A batch is approaching its expiration date',
            'New supplier contact added',
            'Cycle count variance needs review',
        ];

        for ($i = 0; $i < $missing; $i++) {
            DB::table('notifications')->insert([
                'id' => (string) Str::uuid(),
                'type' => 'App\\Notifications\\DemoNotification',
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'data' => json_encode(['message' => $messages[$i % count($messages)]]),
                'read_at' => $i % 3 === 0 ? now()->subHours(fake()->numberBetween(1, 20)) : null,
                'created_at' => now()->subHours(fake()->numberBetween(1, 96)),
                'updated_at' => now(),
            ]);
        }
    }

    private function catalogName(int $index): string
    {
        $names = [
            'Corrugated Shipping Box', 'Pallet Stretch Wrap', 'Packing Tape', 'Bubble Roll',
            'Safety Gloves', 'High-Vis Vest', 'Ear Defenders', 'Safety Glasses',
            'Adjustable Wrench', 'Utility Knife', 'Ratchet Strap', 'Hand Pallet Truck Wheel',
            'Ball Bearing', 'Hex Bolt M8', 'Cable Tie Pack', 'Hydraulic Hose',
            'Degreaser 5L', 'Lubricant Spray', 'Absorbent Pad', 'Barcode Label Roll',
            'Thermal Printer Ribbon', 'Shelf Bin', 'Plastic Pallet', 'Document Pouch',
            'LED Work Light', 'Extension Reel', 'AA Battery Pack', 'Sensor Module',
            'Cardboard Divider', 'Edge Protector', 'Dust Mask', 'Steel Toe Cap',
            'Torque Wrench', 'Spare Filter', 'Washer Assortment', 'Chain Link',
            'Solvent Cleaner', 'Anti-Rust Oil', 'Spill Kit', 'Shipping Label',
        ];

        return $names[$index % count($names)].' '.str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
    }

    private function nextSku(): string
    {
        if ($this->skuCursor === 0) {
            $max = 10000;

            foreach (DB::table('products')->pluck('sku') as $sku) {
                if (preg_match('/(\d+)$/', (string) $sku, $matches)) {
                    $max = max($max, (int) $matches[1]);
                }
            }

            $this->skuCursor = $max;
        }

        $this->skuCursor++;

        return 'SKU-'.str_pad((string) $this->skuCursor, 6, '0', STR_PAD_LEFT);
    }

    private function nextDocumentNumber(string $table, string $prefix): string
    {
        if (! isset($this->numberCursors[$table])) {
            $max = 0;

            foreach (DB::table($table)->pluck('number') as $number) {
                if (preg_match('/(\d+)$/', (string) $number, $matches)) {
                    $max = max($max, (int) $matches[1]);
                }
            }

            $this->numberCursors[$table] = $max;
        }

        $this->numberCursors[$table]++;

        return $prefix.str_pad((string) $this->numberCursors[$table], 6, '0', STR_PAD_LEFT);
    }

    private function uniqueBarcode(): string
    {
        do {
            $code = fake()->ean13();
        } while (
            DB::table('products')->where('barcode', $code)->exists()
            || DB::table('product_barcodes')->where('barcode', $code)->exists()
        );

        return $code;
    }

    private function uniqueSerial(): string
    {
        do {
            $serial = 'SN-'.strtoupper(fake()->bothify('??######'));
        } while (DB::table('product_serial_numbers')->where('serial_number', $serial)->exists());

        return $serial;
    }
}
