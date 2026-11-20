<?php

use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
    $this->user = User::factory()->create();
    $this->user->assignRole('super-admin');
    $this->actingAs($this->user, 'sanctum');
});

it('returns a flat list of matching products and suppliers', function () {
    Product::factory()->create(['name' => 'Harbor Pallet', 'sku' => 'HRB-1']);
    Supplier::factory()->create(['company_name' => 'Harbor Supply']);

    $response = $this->getJson('/api/v1/search?q=Harbor');

    $response->assertOk();

    $rows = collect($response->json('data'));

    expect($rows)->toHaveCount(2)
        ->and($rows->pluck('type')->all())->toContain('Product', 'Supplier')
        ->and($rows->first(fn ($row) => $row['type'] === 'Product')['label'])->toBe('Harbor Pallet · HRB-1')
        ->and($rows->first(fn ($row) => $row['type'] === 'Product')['to'])->toBe('/products')
        ->and($rows->first(fn ($row) => $row['type'] === 'Supplier')['label'])->toBe('Harbor Supply')
        ->and($rows->first(fn ($row) => $row['type'] === 'Supplier')['to'])->toBe('/suppliers');
});
