<?php

use App\Domains\Shared\Enums\MovementType;
use App\Listeners\SendLowStockNotification;
use App\Models\Product;
use App\Models\StockLevel;
use App\Models\User;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use App\Models\WarehouseShelf;
use App\Models\WarehouseZone;
use App\Services\InventoryService;
use Illuminate\Events\CallQueuedListener;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Queue;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

it('forbids creating a product without products.create', function () {
    $role = Role::create(['name' => 'catalog-reader', 'guard_name' => 'web']);
    $role->givePermissionTo(Permission::findOrCreate('products.view', 'web'));

    $user = User::factory()->create();
    $user->assignRole($role);

    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/products', [
            'name' => 'Blocked Box',
            'sku' => 'BLOCK-1',
        ])
        ->assertForbidden();
});

it('writes an activity log row when a product changes', function () {
    $product = Product::factory()->create([
        'category_id' => null,
        'supplier_id' => null,
        'name' => 'Old Name',
    ]);

    $product->update(['name' => 'Renamed Product']);

    $activity = Activity::query()
        ->where('subject_type', Product::class)
        ->where('subject_id', $product->id)
        ->where('event', 'updated')
        ->first();

    expect($activity)->not->toBeNull()
        ->and($activity->properties['attributes']['name'])->toBe('Renamed Product');
});

it('returns 429 on the sixth login attempt in a minute', function () {
    $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10']);

    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    for ($attempt = 0; $attempt < 5; $attempt++) {
        $this->postJson('/api/v1/auth/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ])->assertStatus(422);
    }

    $this->postJson('/api/v1/auth/login', [
        'email' => $user->email,
        'password' => 'wrong-password',
    ])->assertStatus(429);
});

it('queues the low stock listener when stock falls to the minimum', function () {
    Queue::fake();

    $warehouse = Warehouse::factory()->create();
    $zone = WarehouseZone::create(['warehouse_id' => $warehouse->id, 'name' => 'Z', 'code' => 'Z1']);
    $shelf = WarehouseShelf::create(['warehouse_zone_id' => $zone->id, 'name' => 'S', 'code' => 'S1']);
    $location = WarehouseLocation::create([
        'warehouse_id' => $warehouse->id,
        'warehouse_shelf_id' => $shelf->id,
        'code' => 'L1',
    ]);
    $product = Product::factory()->create([
        'category_id' => null,
        'supplier_id' => null,
        'min_stock' => 10,
    ]);

    StockLevel::create([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'location_id' => $location->id,
        'quantity' => 10,
        'reserved_quantity' => 0,
    ]);

    app(InventoryService::class)->recordMovement([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'location_id' => $location->id,
        'quantity' => -1,
        'type' => MovementType::ManualCorrection->value,
    ]);

    Queue::assertPushed(CallQueuedListener::class, function (CallQueuedListener $job) {
        return $job->class === SendLowStockNotification::class;
    });
});
