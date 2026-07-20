<?php

namespace App\Http\Requests\Warehouse;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWarehouseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array { return ['name' => 'sometimes|string', 'code' => 'sometimes|string|unique:warehouses,code,' . $this->route('warehouse')]; }
}