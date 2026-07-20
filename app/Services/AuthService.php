<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function login(string $email, string $password, string $deviceName = 'api'): array
    {
        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => [__('auth.failed')],
            ]);
        }

        $token = $user->createToken($deviceName)->plainTextToken;

        return ['user' => $user->load(['roles', 'permissions', 'employee']), 'token' => $token];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    public function updateProfile(User $user, array $data): User
    {
        $user->update(collect($data)->only(['name', 'email', 'locale', 'theme'])->filter()->all());

        return $user->fresh(['roles', 'permissions', 'employee']);
    }

    public function changePassword(User $user, string $current, string $password): void
    {
        if (! Hash::check($current, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => [__('auth.password')],
            ]);
        }

        $user->update(['password' => $password]);
    }
}