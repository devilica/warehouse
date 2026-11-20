<?php

namespace App\Http\Requests\StockTransfer;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_warehouse_id' => ['required', 'exists:warehouses,id'],
            'to_warehouse_id' => ['required', 'exists:warehouses,id', 'different:from_warehouse_id'],
            'items' => ['nullable', 'array'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity' => ['nullable', 'numeric', 'min:0.001'],
            'items.*.quantity_requested' => ['nullable', 'numeric', 'min:0.001'],
            'items.*.from_location_id' => ['nullable', 'exists:warehouse_locations,id'],
            'items.*.to_location_id' => ['nullable', 'exists:warehouse_locations,id'],
        ];
    }
}