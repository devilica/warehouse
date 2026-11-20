<?php

use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

beforeEach(function () {
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    foreach (['products.view', 'warehouses.view', 'roles.view', 'roles.create', 'roles.update'] as $name) {
        Permission::create(['name' => $name, 'guard_name' => 'web']);
    }

    $role = Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
    $role->givePermissionTo(['roles.view', 'roles.create', 'roles.update']);

    $user = User::factory()->create();
    $user->assignRole('super-admin');
    $this->actingAs($user, 'sanctum');
});

it('creates a role and adds a permission', function () {
    $created = $this->postJson('/api/v1/roles', [
        'name' => 'floor-lead',
        'permissions' => ['products.view'],
    ])->assertCreated()
        ->assertJsonPath('data.name', 'floor-lead');

    expect($created->json('data.permissions'))->toContain('products.view');

    $updated = $this->putJson('/api/v1/roles/'.$created->json('data.id'), [
        'name' => 'floor-lead',
        'permissions' => ['products.view', 'warehouses.view'],
    ])->assertOk()
        ->assertJsonPath('data.name', 'floor-lead');

    expect($updated->json('data.permissions'))->toContain('warehouses.view');
});

it('keeps the super admin role from losing roles view', function () {
    $role = Role::query()->where('name', 'super-admin')->first();

    $this->putJson('/api/v1/roles/'.$role->id, [
        'name' => 'super-admin',
        'permissions' => ['products.view'],
    ])->assertUnprocessable()
        ->assertJsonValidationErrors('permissions');
});
