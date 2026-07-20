<?php

namespace App\Services;

use App\Models\InventoryCount;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryCountService
{
    public function __construct(private InventoryAdjustmentService $adjustmentService, private InventoryService $inventoryService) {}

    public function start(InventoryCount $count): InventoryCount
    {
        if ($count->status !== 'scheduled') {
            throw ValidationException::withMessages(['status' => [__('counts.cannot_start')]]);
        }

        $count->update(['status' => 'in_progress', 'started_at' => now()]);

        return $count->fresh(['items.product', 'warehouse']);
    }

    public function finalize(InventoryCount $count, bool $autoAdjust = false): InventoryCount
    {
        if ($count->status !== 'in_progress') {
            throw ValidationException::withMessages(['status' => [__('counts.cannot_finalize')]]);
        }

        return DB::transaction(function () use ($count, $autoAdjust) {
            $count->load('items');

            if ($autoAdjust) {
                foreach ($count->items as $item) {
                    $variance = ($item->counted_quantity ?? 0) - ($item->expected_quantity ?? 0);
                    if ($variance !== 0) {
                        $this->inventoryService->recordMovement([
                            'product_id' => $item->product_id,
                            'warehouse_id' => $count->warehouse_id,
                            'warehouse_location_id' => $item->warehouse_location_id,
                            'quantity' => $variance,
                            'type' => 'count_adjustment',
                            'reference_type' => InventoryCount::class,
                            'reference_id' => $count->id,
                        ]);
                    }
                }
            }

            $count->update(['status' => 'finalized', 'finalized_at' => now()]);

            return $count->fresh(['items.product']);
        });
    }
}