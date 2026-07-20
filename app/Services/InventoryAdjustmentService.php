<?php

namespace App\Services;

use App\Models\InventoryAdjustment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryAdjustmentService
{
    public function __construct(private InventoryService $inventoryService) {}

    public function approve(InventoryAdjustment $adjustment): InventoryAdjustment
    {
        if ($adjustment->status !== 'pending') {
            throw ValidationException::withMessages(['status' => [__('adjustments.cannot_approve')]]);
        }

        return DB::transaction(function () use ($adjustment) {
            $adjustment->load('items');

            foreach ($adjustment->items as $item) {
                $this->inventoryService->recordMovement([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $adjustment->warehouse_id,
                    'warehouse_location_id' => $item->warehouse_location_id,
                    'quantity' => $item->quantity_delta,
                    'type' => $item->reason ?? 'manual_correction',
                    'reference_type' => InventoryAdjustment::class,
                    'reference_id' => $adjustment->id,
                    'user_id' => auth()->id(),
                ]);
            }

            $adjustment->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => auth()->id(),
            ]);

            return $adjustment->fresh(['items.product', 'warehouse']);
        });
    }
}