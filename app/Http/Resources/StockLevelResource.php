<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StockLevelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'warehouse_id' => $this->warehouse_id,
            'location_id' => $this->location_id,
            'batch_id' => $this->batch_id,
            'quantity' => $this->quantity,
            'reserved_quantity' => $this->reserved_quantity,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'product' => $this->whenLoaded('product', fn () => new ProductResource($this->product)),
            'warehouse' => $this->whenLoaded('warehouse', fn () => new WarehouseResource($this->warehouse)),
            'location' => $this->whenLoaded('location', fn () => new WarehouseLocationResource($this->location)),
        ];
    }
}
