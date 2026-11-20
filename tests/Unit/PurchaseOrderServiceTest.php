<?php

use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderService;
use Illuminate\Validation\ValidationException;

it('sends a draft purchase order', function () {
    $order = PurchaseOrder::factory()->create(['status' => 'draft']);

    $sent = app(PurchaseOrderService::class)->send($order);

    expect($sent->status)->toBe('sent')
        ->and($sent->sent_at)->not->toBeNull();
});

it('rejects sending a purchase order that is already sent', function () {
    $order = PurchaseOrder::factory()->create(['status' => 'sent']);

    app(PurchaseOrderService::class)->send($order);
})->throws(ValidationException::class);

it('rejects closing a draft purchase order', function () {
    $order = PurchaseOrder::factory()->create(['status' => 'draft']);

    app(PurchaseOrderService::class)->close($order);
})->throws(ValidationException::class);

it('closes a sent purchase order', function () {
    $order = PurchaseOrder::factory()->create(['status' => 'sent']);

    $closed = app(PurchaseOrderService::class)->close($order);

    expect($closed->status)->toBe('closed')
        ->and($closed->closed_at)->not->toBeNull();
});
