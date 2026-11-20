<?php

use App\Models\Product;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
    $this->user = User::factory()->create();
    $this->user->assignRole('super-admin');
    $this->actingAs($this->user, 'sanctum');
});

it('paginates products ten at a time', function () {
    Product::factory()->count(12)->create();

    $response = $this->getJson('/api/v1/products');

    $response->assertOk()
        ->assertJsonPath('meta.per_page', 10)
        ->assertJsonPath('meta.total', 12)
        ->assertJsonPath('meta.last_page', 2)
        ->assertJsonCount(10, 'data');

    $this->getJson('/api/v1/products?page=2')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('meta.current_page', 2);
});

it('filters and sorts products', function () {
    Product::factory()->create(['name' => 'Alpha Bolt', 'sku' => 'ALP-1']);
    Product::factory()->create(['name' => 'Zebra Widget', 'sku' => 'ZEB-1']);

    $filtered = $this->getJson('/api/v1/products?filter[search]=Alpha');
    $filtered->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.name', 'Alpha Bolt');

    $this->getJson('/api/v1/products?sort=name')
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Alpha Bolt');

    $this->getJson('/api/v1/products?sort=-name')
        ->assertOk()
        ->assertJsonPath('data.0.name', 'Zebra Widget');
});
