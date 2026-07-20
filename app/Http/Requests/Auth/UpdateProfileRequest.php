<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array { return ['name' => 'sometimes|string|max:255', 'email' => 'sometimes|email|unique:users,email,' . $this->user()->id, 'locale' => 'sometimes|in:en,de,bs', 'theme' => 'sometimes|in:light,dark']; }
}