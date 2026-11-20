<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Support\DocumentNumber;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseOrderService
{
    public function send(PurchaseOrder $order): PurchaseOrder
    {
        if ($order->status !== 'draft') {
            throw ValidationException::withMessages(['status' => [__('purchase_orders.cannot_send')]]);
        }

        $order->update(['status' => 'sent', 'sent_at' => now()]);

        return $order->fresh(['supplier', 'items.product']);
    }

    public function close(PurchaseOrder $order): PurchaseOrder
    {
        if (! in_array($order->status, ['sent', 'partially_received'], true)) {
            throw ValidationException::withMessages(['status' => [__('purchase_orders.cannot_close')]]);
        }

        $order->update(['status' => 'closed', 'closed_at' => now()]);

        return $order->fresh(['supplier', 'items.product']);
    }

    public function create(array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($data) {
            $items = $data['items'] ?? [];
            unset($data['items']);

            $order = PurchaseOrder::create(array_merge($data, [
                'number' => $data['number'] ?? DocumentNumber::make('PO-'),
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]));

            foreach ($items as $item) {
                $quantity = $item['quantity_ordered'] ?? $item['quantity'] ?? null;
                if ($quantity === null) {
                    throw ValidationException::withMessages(['items' => ['Each item needs a quantity.']]);
                }
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity_ordered' => $quantity,
                    'unit_price' => $item['unit_price'] ?? 0,
                ]);
            }

            return $order->load(['supplier', 'items.product']);
        });
    }
}