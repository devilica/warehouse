<?php

use App\Models\PurchaseOrder;
use App\Models\User;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    Role::create(['name' => 'super-admin', 'guard_name' => 'web']);
    $this->user = User::factory()->create();
    $this->user->assignRole('super-admin');
    $this->actingAs($this->user, 'sanctum');
});

it('sends a draft purchase order over http', function () {
    $order = PurchaseOrder::factory()->create(['status' => 'draft']);

    $this->postJson("/api/v1/purchase-orders/{$order->id}/send")
        ->assertOk()
        ->assertJsonPath('data.status', 'sent');
});

it('rejects sending a purchase order that was already sent', function () {
    $order = PurchaseOrder::factory()->create(['status' => 'sent']);

    $this->postJson("/api/v1/purchase-orders/{$order->id}/send")
        ->assertUnprocessable();
});
