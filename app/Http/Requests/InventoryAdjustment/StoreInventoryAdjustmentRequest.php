<?php

namespace App\Http\Requests\InventoryAdjustment;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'reason' => ['nullable', 'string'],
            'items' => ['nullable', 'array'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.location_id' => ['nullable', 'exists:warehouse_locations,id'],
            'items.*.warehouse_location_id' => ['nullable', 'exists:warehouse_locations,id'],
            'items.*.quantity_change' => ['nullable', 'numeric'],
            'items.*.quantity_delta' => ['nullable', 'numeric'],
        ];
    }
}