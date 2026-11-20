<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PurchaseOrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'number' => $this->number,
            'supplier_id' => $this->supplier_id,
            'status' => $this->status,
            'expected_delivery_date' => $this->expected_delivery_date,
            'created_by' => $this->created_by,
            'sent_at' => $this->sent_at,
            'closed_at' => $this->closed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'supplier' => $this->whenLoaded('supplier', fn () => new SupplierResource($this->supplier)),
            'items' => $this->whenLoaded('items', fn () => PurchaseOrderItemResource::collection($this->items)),
        ];
    }
}
