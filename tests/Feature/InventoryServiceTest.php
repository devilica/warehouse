<?php

use App\Models\Product;
use App\Models\StockLevel;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use App\Models\WarehouseShelf;
use App\Models\WarehouseZone;
use App\Services\InventoryService;
use App\Domains\Shared\Enums\MovementType;

function createStockContext(): array
{
    $warehouse = Warehouse::factory()->create();
    $zone = WarehouseZone::create([
        'warehouse_id' => $warehouse->id,
        'name' => 'Zone A',
        'code' => 'A1',
    ]);
    $shelf = WarehouseShelf::create([
        'warehouse_zone_id' => $zone->id,
        'name' => 'Shelf 1',
        'code' => 'S1',
    ]);
    $location = WarehouseLocation::create([
        'warehouse_id' => $warehouse->id,
        'warehouse_shelf_id' => $shelf->id,
        'code' => 'LOC-001',
    ]);
    $product = Product::factory()->create([
        'category_id' => null,
        'supplier_id' => null,
    ]);

    StockLevel::create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'location_id' => $location->id,
        'quantity' => 100,
        'reserved_quantity' => 0,
    ]);

    return compact('warehouse', 'location', 'product');
}

it('records a stock movement via API service layer', function () {
    ['warehouse' => $warehouse, 'location' => $location, 'product' => $product] = createStockContext();

    $service = app(InventoryService::class);
    $transaction = $service->recordMovement([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'location_id' => $location->id,
        'quantity' => -10,
        'type' => MovementType::ManualCorrection->value,
    ]);

    expect((float) $transaction->quantity_change)->toBe(-10.0);
    expect((float) StockLevel::first()->quantity)->toBe(90.0);
});
