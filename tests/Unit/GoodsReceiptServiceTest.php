<?php

use App\Models\GoodsReceipt;
use App\Models\Product;
use App\Models\StockLevel;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use App\Models\WarehouseShelf;
use App\Models\WarehouseZone;
use App\Services\GoodsReceiptService;
use Illuminate\Validation\ValidationException;

function receiptWithItem(float $quantity = 4): GoodsReceipt
{
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
        'min_stock' => 0,
    ]);

    $receipt = new GoodsReceipt();
    $receipt->forceFill([
        'number' => 'GR-'.fake()->unique()->numerify('####'),
        'status' => 'draft',
    ])->save();
    $item = $receipt->items()->make();
    $item->forceFill([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'location_id' => $location->id,
        'quantity_received' => $quantity,
    ])->save();

    return $receipt;
}

it('confirms a goods receipt and increases stock', function () {
    $receipt = receiptWithItem(4);

    $confirmed = app(GoodsReceiptService::class)->confirm($receipt);

    expect($confirmed->status)->toBe('confirmed')
        ->and((float) StockLevel::first()->quantity)->toBe(4.0);
});

it('rejects confirming a goods receipt twice', function () {
    $receipt = receiptWithItem();
    $service = app(GoodsReceiptService::class);

    $service->confirm($receipt);

    $service->confirm($receipt->fresh());
})->throws(ValidationException::class);
