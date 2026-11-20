<?php

use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole('super-admin');
    $this->actingAs($user, 'sanctum');
});

it('creates a category and renames it', function () {
    $created = $this->postJson('/api/v1/categories', ['name' => 'Hardware'])
        ->assertCreated()
        ->assertJsonPath('data.name', 'Hardware')
        ->assertJsonPath('data.slug', 'hardware');

    $this->putJson('/api/v1/categories/'.$created->json('data.id'), ['name' => 'Tools'])
        ->assertOk()
        ->assertJsonPath('data.name', 'Tools')
        ->assertJsonPath('data.slug', 'tools');
});
