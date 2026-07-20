<?php

namespace App\Services;

use App\Events\StockTransferCompleted;
use App\Models\StockTransfer;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockTransferService
{
    public function __construct(private InventoryService $inventoryService) {}

    public function approve(StockTransfer $transfer): StockTransfer
    {
        $this->assertStatus($transfer, 'draft', __('transfers.cannot_approve'));
        $transfer->update(['status' => 'approved', 'approved_at' => now(), 'approved_by' => auth()->id()]);

        return $transfer->fresh(['items.product', 'fromWarehouse', 'toWarehouse']);
    }

    public function ship(StockTransfer $transfer): StockTransfer
    {
        $this->assertStatus($transfer, 'approved', __('transfers.cannot_ship'));

        return DB::transaction(function () use ($transfer) {
            foreach ($transfer->items as $item) {
                $this->inventoryService->recordMovement([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $transfer->from_warehouse_id,
                    'warehouse_location_id' => $item->from_warehouse_location_id,
                    'quantity' => -abs($item->quantity),
                    'type' => 'transfer_out',
                    'reference_type' => StockTransfer::class,
                    'reference_id' => $transfer->id,
                ]);
            }

            $transfer->update(['status' => 'shipped', 'shipped_at' => now()]);

            return $transfer->fresh(['items.product']);
        });
    }

    public function receive(StockTransfer $transfer): StockTransfer
    {
        $this->assertStatus($transfer, 'shipped', __('transfers.cannot_receive'));

        return DB::transaction(function () use ($transfer) {
            foreach ($transfer->items as $item) {
                $this->inventoryService->recordMovement([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $transfer->to_warehouse_id,
                    'warehouse_location_id' => $item->to_warehouse_location_id,
                    'quantity' => abs($item->quantity),
                    'type' => 'transfer_in',
                    'reference_type' => StockTransfer::class,
                    'reference_id' => $transfer->id,
                ]);
            }

            $transfer->update(['status' => 'received', 'received_at' => now()]);

            return $transfer->fresh(['items.product']);
        });
    }

    public function complete(StockTransfer $transfer): StockTransfer
    {
        $this->assertStatus($transfer, 'received', __('transfers.cannot_complete'));
        $transfer->update(['status' => 'completed', 'completed_at' => now()]);
        event(new StockTransferCompleted(stockTransfer: $transfer));

        return $transfer->fresh(['items.product']);
    }

    private function assertStatus(StockTransfer $transfer, string $expected, string $message): void
    {
        if ($transfer->status !== $expected) {
            throw ValidationException::withMessages(['status' => [$message]]);
        }
    }
}