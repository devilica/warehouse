<?php

namespace App\Http\Requests\InventoryAdjustment;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array { return ['warehouse_id' => 'required|exists:warehouses,id', 'reason' => 'nullable|string', 'items' => 'required|array|min:1']; }
}