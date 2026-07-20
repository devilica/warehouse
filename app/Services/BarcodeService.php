<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductBarcode;
use Picqer\Barcode\BarcodeGeneratorPNG;

class BarcodeService
{
    public function generate(Product $product, string $type = 'EAN13'): ProductBarcode
    {
        $code = $product->sku ?: str_pad((string) $product->id, 12, '0', STR_PAD_LEFT);
        $generator = new BarcodeGeneratorPNG();

        return ProductBarcode::create([
            'product_id' => $product->id,
            'code' => $code,
            'type' => $type,
            'image' => base64_encode($generator->getBarcode($code, $generator::TYPE_CODE_128)),
            'is_primary' => ! $product->barcodes()->exists(),
        ]);
    }

    public function findByBarcode(string $code): ?Product
    {
        $barcode = ProductBarcode::where('code', $code)->first();

        return $barcode?->product;
    }
}