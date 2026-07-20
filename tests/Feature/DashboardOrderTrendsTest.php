<?php

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user, 'sanctum');
});

it('returns daily order trends', function () {
    $product = Product::factory()->create();
    $po = PurchaseOrder::factory()->create([
        'status' => 'sent',
        'created_by' => $this->user->id,
        'sent_at' => now(),
        'created_at' => now(),
    ]);

    DB::table('purchase_order_items')->insert([
        'purchase_order_id' => $po->id,
        'product_id' => $product->id,
        'quantity_ordered' => 25,
        'quantity_received' => 0,
        'unit_price' => 10,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->getJson('/api/v1/dashboard/order-trends?period=day');

    $response->assertOk()
        ->assertJsonPath('data.period', 'day')
        ->assertJsonStructure([
            'data' => ['period', 'labels', 'units_ordered', 'order_count', 'totals'],
        ]);

    expect($response->json('data.labels'))->toHaveCount(30);
    expect($response->json('data.totals.units'))->toBe(25);
});

it('returns monthly order trends', function () {
    $this->getJson('/api/v1/dashboard/order-trends?period=month')
        ->assertOk()
        ->assertJsonPath('data.period', 'month');

    expect($this->getJson('/api/v1/dashboard/order-trends?period=month')->json('data.labels'))->toHaveCount(12);
});

it('rejects invalid period', function () {
    $this->getJson('/api/v1/dashboard/order-trends?period=year')
        ->assertUnprocessable();
});
