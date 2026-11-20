<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string'],
            'sku' => ['sometimes', 'string', Rule::unique('products', 'sku')->ignore($this->route('product'))],
            'category_id' => ['nullable', 'exists:product_categories,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
        ];
    }
}
