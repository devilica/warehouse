<?php

namespace App\Domains\Inventory\DTOs;

use App\Domains\Shared\Enums\MovementType;

readonly class MovementDTO
{
    public function __construct(
        public MovementType $type,
        public int $productId,
        public int $warehouseId,
        public int $locationId,
        public float $quantityChange,
        public ?int $userId = null,
        public ?string $referenceType = null,
        public ?int $referenceId = null,
        public ?array $metadata = null,
        public ?int $batchId = null,
        public bool $allowNegative = false,
    ) {}
}
