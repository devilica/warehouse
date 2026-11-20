<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sku' => $this->sku,
            'barcode' => $this->barcode,
            'name' => $this->name,
            'description' => $this->description,
            'category_id' => $this->category_id,
            'supplier_id' => $this->supplier_id,
            'purchase_price' => $this->purchase_price,
            'selling_price' => $this->selling_price,
            'tax_rate' => $this->tax_rate,
            'unit' => $this->unit,
            'min_stock' => $this->min_stock,
            'max_stock' => $this->max_stock,
            'weight' => $this->weight,
            'dimensions' => $this->dimensions,
            'tracks_serial' => $this->tracks_serial,
            'tracks_batch' => $this->tracks_batch,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'category' => $this->whenLoaded('category', fn () => new ProductCategoryResource($this->category)),
            'supplier' => $this->whenLoaded('supplier', fn () => new SupplierResource($this->supplier)),
            'barcodes' => $this->whenLoaded('barcodes', fn () => ProductBarcodeResource::collection($this->barcodes)),
            'stock_levels' => $this->whenLoaded('stockLevels', fn () => StockLevelResource::collection($this->stockLevels)),
        ];
    }
}
