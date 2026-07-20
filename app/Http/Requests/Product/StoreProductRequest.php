<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array { return ['name' => 'required|string', 'sku' => 'required|string|unique:products', 'category_id' => 'nullable|exists:product_categories,id', 'supplier_id' => 'nullable|exists:suppliers,id']; }
}