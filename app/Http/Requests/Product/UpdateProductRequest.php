<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array { return ['name' => 'sometimes|string', 'sku' => 'sometimes|string|unique:products,sku,' . $this->route('product')]; }
}