<?php

namespace App\Http\Requests\Employee;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array { return ['first_name' => 'sometimes|string', 'last_name' => 'sometimes|string', 'department_id' => 'nullable|exists:departments,id']; }
}