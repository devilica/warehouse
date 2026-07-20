<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

it('logs in with valid credentials', function () {
    Role::create(['name' => 'viewer', 'guard_name' => 'web']);
    $user = User::factory()->create(['password' => Hash::make('password')]);
    $user->assignRole('viewer');

    $response = $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertOk()->assertJsonStructure(['data' => ['token', 'user']]);
});

it('rejects invalid credentials', function () {
    $user = User::factory()->create(['password' => Hash::make('password')]);

    $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertStatus(422);
});

it('returns authenticated user profile', function () {
    $user = User::factory()->create();
    $this->actingAs($user, 'sanctum');

    $this->getJson('/api/v1/auth/me')->assertOk()->assertJsonPath('data.email', $user->email);
});