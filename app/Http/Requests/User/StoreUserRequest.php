<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array { return ['name' => 'required|string', 'email' => 'required|email|unique:users', 'password' => 'required|string|min:8', 'role' => 'nullable|string']; }
}