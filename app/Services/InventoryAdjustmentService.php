<?php

namespace App\Services;

use App\Models\InventoryAdjustment;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class InventoryAdjustmentService
{
    public function __construct(private InventoryService $inventoryService) {}

    public function approve(InventoryAdjustment $adjustment): InventoryAdjustment
    {
        if ($adjustment->status !== 'pending_approval') {
            throw ValidationException::withMessages(['status' => [__('adjustments.cannot_approve')]]);
        }

        return DB::transaction(function () use ($adjustment) {
            $adjustment->load('items');

            $type = in_array($adjustment->reason, ['manual_correction', 'damage'], true)
                ? $adjustment->reason
                : 'manual_correction';

            foreach ($adjustment->items as $item) {
                try {
                    $this->inventoryService->recordMovement([
                        'product_id' => $item->product_id,
                        'warehouse_id' => $adjustment->warehouse_id,
                        'warehouse_location_id' => $item->warehouse_location_id ?? $item->location_id,
                        'quantity' => $item->quantity_delta ?? $item->quantity_change,
                        'type' => $type,
                        'reference_type' => InventoryAdjustment::class,
                        'reference_id' => $adjustment->id,
                        'user_id' => auth()->id(),
                    ]);
                } catch (InvalidArgumentException $exception) {
                    throw ValidationException::withMessages(['quantity' => [$exception->getMessage()]]);
                }
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