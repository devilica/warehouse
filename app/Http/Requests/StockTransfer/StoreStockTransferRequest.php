<?php

namespace App\Http\Requests\StockTransfer;

use Illuminate\Foundation\Http\FormRequest;

class StoreStockTransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array { return ['from_warehouse_id' => 'required|exists:warehouses,id', 'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id', 'items' => 'required|array|min:1']; }
}