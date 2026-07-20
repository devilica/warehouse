<?php

use App\Models\Product;
use App\Models\StockLevel;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use App\Models\WarehouseShelf;
use App\Models\WarehouseZone;
use App\Services\InventoryService;
use App\Domains\Shared\Enums\MovementType;

it('prevents negative stock levels', function () {
    $warehouse = Warehouse::factory()->create();
    $zone = WarehouseZone::create(['warehouse_id' => $warehouse->id, 'name' => 'Z', 'code' => 'Z1']);
    $shelf = WarehouseShelf::create(['warehouse_zone_id' => $zone->id, 'name' => 'S', 'code' => 'S1']);
    $location = WarehouseLocation::create([
        'warehouse_id' => $warehouse->id,
        'warehouse_shelf_id' => $shelf->id,
        'code' => 'L1',
    ]);
    $product = Product::factory()->create([
        'category_id' => null,
        'supplier_id' => null,
    ]);

    StockLevel::create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'location_id' => $location->id,
        'quantity' => 5,
        'reserved_quantity' => 0,
    ]);

    app(InventoryService::class)->recordMovement([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'location_id' => $location->id,
        'quantity' => -10,
        'type' => MovementType::ManualCorrection->value,
    ]);
})->throws(\InvalidArgumentException::class);
