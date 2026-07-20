<?php

namespace App\Domains\Inventory\Services;

use App\Domains\Inventory\DTOs\MovementDTO;
use App\Domains\Shared\Enums\MovementType;
use App\Models\InventoryTransaction;
use App\Models\StockLevel;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use RuntimeException;

class InventoryService
{
    public function recordMovement(MovementDTO $dto): InventoryTransaction
    {
        return DB::transaction(function () use ($dto) {
            $stock = StockLevel::query()
                ->where([
                    'product_id' => $dto->productId,
                    'warehouse_id' => $dto->warehouseId,
                    'location_id' => $dto->locationId,
                ])
                ->lockForUpdate()
                ->first();

            if ($stock === null) {
                $stock = StockLevel::create([
                    'product_id' => $dto->productId,
                    'warehouse_id' => $dto->warehouseId,
                    'location_id' => $dto->locationId,
                    'batch_id' => $dto->batchId,
                    'quantity' => 0,
                    'reserved_quantity' => 0,
                ]);

                $stock = StockLevel::query()
                    ->whereKey($stock->id)
                    ->lockForUpdate()
                    ->firstOrFail();
            }

            $quantityBefore = (float) $stock->quantity;
            $quantityAfter = $quantityBefore + $dto->quantityChange;

            if (! $dto->allowNegative && $quantityAfter < 0) {
                throw new InvalidArgumentException(
                    "Insufficient stock for product {$dto->productId}. Available: {$quantityBefore}, requested change: {$dto->quantityChange}."
                );
            }

            $transaction = InventoryTransaction::create([
                'type' => $dto->type->value,
                'product_id' => $dto->productId,
                'warehouse_id' => $dto->warehouseId,
                'location_id' => $dto->locationId,
                'quantity_change' => $dto->quantityChange,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $quantityAfter,
                'reference_type' => $dto->referenceType,
                'reference_id' => $dto->referenceId,
                'user_id' => $dto->userId,
                'metadata' => $dto->metadata,
            ]);

            $stock->update(['quantity' => $quantityAfter]);

            return $transaction;
        });
    }

    public function reserveStock(
        int $productId,
        int $warehouseId,
        int $locationId,
        float $quantity,
        ?int $userId = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?array $metadata = null,
    ): InventoryTransaction {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Reservation quantity must be greater than zero.');
        }

        return DB::transaction(function () use (
            $productId,
            $warehouseId,
            $locationId,
            $quantity,
            $userId,
            $referenceType,
            $referenceId,
            $metadata,
        ) {
            $stock = $this->lockStockLevel($productId, $warehouseId, $locationId);

            $available = (float) $stock->quantity - (float) $stock->reserved_quantity;

            if ($available < $quantity) {
                throw new RuntimeException(
                    "Insufficient available stock to reserve. Available: {$available}, requested: {$quantity}."
                );
            }

            $stock->update([
                'reserved_quantity' => (float) $stock->reserved_quantity + $quantity,
            ]);

            return InventoryTransaction::create([
                'type' => MovementType::Reservation->value,
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'location_id' => $locationId,
                'quantity_change' => 0,
                'quantity_before' => (float) $stock->quantity,
                'quantity_after' => (float) $stock->quantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'user_id' => $userId,
                'metadata' => array_merge($metadata ?? [], ['reserved_quantity' => $quantity]),
            ]);
        });
    }

    public function releaseReservation(
        int $productId,
        int $warehouseId,
        int $locationId,
        float $quantity,
        ?int $userId = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?array $metadata = null,
    ): InventoryTransaction {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Release quantity must be greater than zero.');
        }

        return DB::transaction(function () use (
            $productId,
            $warehouseId,
            $locationId,
            $quantity,
            $userId,
            $referenceType,
            $referenceId,
            $metadata,
        ) {
            $stock = $this->lockStockLevel($productId, $warehouseId, $locationId);

            if ((float) $stock->reserved_quantity < $quantity) {
                throw new RuntimeException(
                    "Cannot release more than reserved quantity. Reserved: {$stock->reserved_quantity}, requested: {$quantity}."
                );
            }

            $stock->update([
                'reserved_quantity' => (float) $stock->reserved_quantity - $quantity,
            ]);

            return InventoryTransaction::create([
                'type' => MovementType::ReservationRelease->value,
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'location_id' => $locationId,
                'quantity_change' => 0,
                'quantity_before' => (float) $stock->quantity,
                'quantity_after' => (float) $stock->quantity,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'user_id' => $userId,
                'metadata' => array_merge($metadata ?? [], ['released_quantity' => $quantity]),
            ]);
        });
    }

    private function lockStockLevel(int $productId, int $warehouseId, int $locationId): StockLevel
    {
        $stock = StockLevel::query()
            ->where([
                'product_id' => $productId,
                'warehouse_id' => $warehouseId,
                'location_id' => $locationId,
            ])
            ->lockForUpdate()
            ->first();

        if ($stock === null) {
            throw new RuntimeException(
                "Stock level not found for product {$productId} at warehouse {$warehouseId}, location {$locationId}."
            );
        }

        return $stock;
    }
}
