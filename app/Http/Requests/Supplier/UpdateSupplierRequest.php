<?php

namespace App\Http\Requests\Supplier;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSupplierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array { return ['name' => 'sometimes|string', 'code' => 'sometimes|string|unique:suppliers,code,' . $this->route('supplier'), 'email' => 'nullable|email']; }
}