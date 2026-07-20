<?php

namespace App\Http\Requests\InventoryCount;

use Illuminate\Foundation\Http\FormRequest;

class StoreInventoryCountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array { return ['warehouse_id' => 'required|exists:warehouses,id', 'type' => 'required|in:full,partial', 'scheduled_at' => 'nullable|date']; }
}