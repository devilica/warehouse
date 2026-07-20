<?php

namespace App\Http\Requests\GoodsReceipt;

use Illuminate\Foundation\Http\FormRequest;

class StoreGoodsReceiptRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array { return ['purchase_order_id' => 'required|exists:purchase_orders,id', 'warehouse_id' => 'required|exists:warehouses,id']; }
}