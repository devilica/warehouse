<?php

use App\Models\InventoryAdjustment;
use App\Models\Warehouse;
use App\Services\InventoryAdjustmentService;

it('approves an adjustment that is waiting for approval', function () {
    $warehouse = Warehouse::factory()->create();
    $adjustment = new InventoryAdjustment;
    $adjustment->forceFill([
        'number' => 'ADJ-TEST-1',
        'warehouse_id' => $warehouse->id,
        'status' => 'pending_approval',
        'reason' => 'Cycle count',
    ])->save();

    $approved = app(InventoryAdjustmentService::class)->approve($adjustment);

    expect($approved->status)->toBe('approved')
        ->and($approved->approved_at)->not->toBeNull();
});
