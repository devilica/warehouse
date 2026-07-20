<?php

namespace App\Services;

use App\Domains\Inventory\DTOs\MovementDTO;
use App\Domains\Inventory\Services\InventoryService as DomainInventoryService;
use App\Domains\Shared\Enums\MovementType;
use App\Events\LowStockDetected;
use App\Events\StockMovementRecorded;
use App\Models\InventoryTransaction;
use App\Models\Product;

class InventoryService
{
    public function __construct(private DomainInventoryService $inventoryService) {}

    public function recordMovement(array $data): InventoryTransaction
    {
        $type = $data['type'] instanceof MovementType
            ? $data['type']
            : MovementType::from($data['type']);

        $transaction = $this->inventoryService->recordMovement(new MovementDTO(
            type: $type,
            productId: $data['product_id'],
            warehouseId: $data['warehouse_id'],
            locationId: $data['location_id'] ?? $data['warehouse_location_id'],
            quantityChange: (float) ($data['quantity'] ?? $data['quantity_change']),
            userId: $data['user_id'] ?? auth()->id(),
            referenceType: $data['reference_type'] ?? null,
            referenceId: $data['reference_id'] ?? null,
            metadata: $data['metadata'] ?? null,
            batchId: $data['batch_id'] ?? null,
            allowNegative: $data['allow_negative'] ?? false,
        ));

        event(new StockMovementRecorded(
            product_id: $data['product_id'],
            warehouse_id: $data['warehouse_id'],
            quantity: (float) ($data['quantity'] ?? $data['quantity_change']),
            type: $type->value
        ));

        $product = Product::find($data['product_id']);
        $stockQty = $transaction->quantity_after;

        if ($product && $stockQty <= ($product->min_stock ?? 0)) {
            event(new LowStockDetected(
                product_id: $data['product_id'],
                warehouse_id: $data['warehouse_id'],
                current_quantity: $stockQty,
                reorder_level: $product->min_stock ?? 0
            ));
        }

        return $transaction;
    }
}
