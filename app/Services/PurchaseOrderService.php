<?php

namespace App\Services;

use App\Models\PurchaseOrder;
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
                'status' => 'draft',
                'created_by' => auth()->id(),
            ]));

            foreach ($items as $item) {
                $order->items()->create($item);
            }

            return $order->load(['supplier', 'items.product']);
        });
    }
}