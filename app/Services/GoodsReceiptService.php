<?php

namespace App\Services;

use App\Events\GoodsReceiptConfirmed;
use App\Models\GoodsReceipt;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GoodsReceiptService
{
    public function __construct(private InventoryService $inventoryService) {}

    public function confirm(GoodsReceipt $receipt): GoodsReceipt
    {
        if ($receipt->status === 'confirmed') {
            throw ValidationException::withMessages(['status' => [__('goods_receipts.already_confirmed')]]);
        }

        return DB::transaction(function () use ($receipt) {
            $receipt->load('items.product');

            foreach ($receipt->items as $item) {
                $this->inventoryService->recordMovement([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $receipt->warehouse_id,
                    'warehouse_location_id' => $item->warehouse_location_id,
                    'quantity' => $item->quantity_received,
                    'type' => 'purchase',
                    'reference_type' => GoodsReceipt::class,
                    'reference_id' => $receipt->id,
                    'user_id' => auth()->id(),
                ]);
            }

            $receipt->update(['status' => 'confirmed', 'confirmed_at' => now(), 'confirmed_by' => auth()->id()]);

            event(new GoodsReceiptConfirmed(goodsReceipt: $receipt));

            return $receipt->fresh(['items.product', 'purchaseOrder']);
        });
    }
}