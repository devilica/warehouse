<?php

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
    $user = User::factory()->create();
    $user->assignRole('super-admin');
    $this->actingAs($user, 'sanctum');
});

it('updates a product sku without using the model as column names', function () {
    $product = Product::factory()->create(['sku' => 'SKU-7445']);

    $this->putJson('/api/v1/products/'.$product->id, [
        'name' => 'Updated name',
        'sku' => 'SKU-7446',
        'category_id' => $product->category_id,
        'supplier_id' => $product->supplier_id,
    ])->assertOk()
        ->assertJsonPath('data.sku', 'SKU-7446')
        ->assertJsonPath('data.category_id', $product->category_id)
        ->assertJsonPath('data.supplier_id', $product->supplier_id);

    expect($product->fresh()->sku)->toBe('SKU-7446');
});

it('allows a product to keep its current sku', function () {
    $product = Product::factory()->create(['sku' => 'SKU-7445']);

    $this->putJson('/api/v1/products/'.$product->id, [
        'sku' => 'SKU-7445',
    ])->assertOk()
        ->assertJsonPath('data.sku', 'SKU-7445');
});

it('creates a supplier from the company name', function () {
    $this->postJson('/api/v1/suppliers', [
        'company_name' => 'Acme Supplies',
        'email' => 'orders@acme.test',
        'phone' => '123456',
    ])->assertCreated()
        ->assertJsonPath('data.company_name', 'Acme Supplies');
});

it('creates a purchase order header without line items', function () {
    $supplier = Supplier::factory()->create();

    $this->postJson('/api/v1/purchase-orders', [
        'supplier_id' => $supplier->id,
    ])->assertCreated()
        ->assertJsonPath('data.status', 'draft');

    $order = PurchaseOrder::query()->where('supplier_id', $supplier->id)->first();

    expect($order)->not->toBeNull()
        ->and($order->number)->toStartWith('PO-');
});
