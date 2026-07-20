<?php

declare(strict_types=1);

$base = dirname(__DIR__);

function writeFile(string $path, string $content): void
{
    $dir = dirname($path);
    if (! is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents($path, $content);
}

$created = [];
function track(string $path, string $content): void
{
    global $created, $base;
    writeFile($base . '/' . $path, $content);
    $created[] = $path;
}

track('routes/api.php', <<<'PHP'
<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\AuditLogController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\EmployeeController;
use App\Http\Controllers\Api\V1\GoodsReceiptController;
use App\Http\Controllers\Api\V1\InventoryAdjustmentController;
use App\Http\Controllers\Api\V1\InventoryCountController;
use App\Http\Controllers\Api\V1\InventoryTransactionController;
use App\Http\Controllers\Api\V1\NotificationController;
use App\Http\Controllers\Api\V1\ProductCategoryController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\PurchaseOrderController;
use App\Http\Controllers\Api\V1\ReportController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\StockLevelController;
use App\Http\Controllers\Api\V1\StockTransferController;
use App\Http\Controllers\Api\V1\SupplierController;
use App\Http\Controllers\Api\V1\UserController;
use App\Http\Controllers\Api\V1\WarehouseController;
use App\Http\Controllers\Api\V1\WarehouseLocationController;
use App\Http\Controllers\Api\V1\WarehouseShelfController;
use App\Http\Controllers\Api\V1\WarehouseZoneController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::put('auth/profile', [AuthController::class, 'updateProfile']);
        Route::put('auth/password', [AuthController::class, 'changePassword']);

        Route::apiResource('users', UserController::class);
        Route::apiResource('employees', EmployeeController::class);
        Route::apiResource('suppliers', SupplierController::class);
        Route::apiResource('warehouses', WarehouseController::class);
        Route::apiResource('warehouses.zones', WarehouseZoneController::class)->shallow();
        Route::apiResource('warehouses.zones.shelves', WarehouseShelfController::class)->shallow();
        Route::apiResource('warehouses.zones.shelves.locations', WarehouseLocationController::class)->shallow();
        Route::apiResource('categories', ProductCategoryController::class);
        Route::apiResource('products', ProductController::class);
        Route::get('products/by-barcode/{code}', [ProductController::class, 'byBarcode']);
        Route::post('products/{product}/barcodes/generate', [ProductController::class, 'generateBarcode']);

        Route::apiResource('inventory-transactions', InventoryTransactionController::class)->only(['index', 'show']);
        Route::apiResource('stock-levels', StockLevelController::class)->only(['index', 'show']);

        Route::apiResource('purchase-orders', PurchaseOrderController::class)->except(['update', 'destroy']);
        Route::post('purchase-orders/{purchase_order}/send', [PurchaseOrderController::class, 'send']);
        Route::post('purchase-orders/{purchase_order}/close', [PurchaseOrderController::class, 'close']);

        Route::apiResource('goods-receipts', GoodsReceiptController::class)->except(['update', 'destroy']);
        Route::post('goods-receipts/{goods_receipt}/confirm', [GoodsReceiptController::class, 'confirm']);

        Route::apiResource('inventory-adjustments', InventoryAdjustmentController::class)->except(['update', 'destroy']);
        Route::post('inventory-adjustments/{inventory_adjustment}/approve', [InventoryAdjustmentController::class, 'approve']);

        Route::apiResource('stock-transfers', StockTransferController::class)->except(['update', 'destroy']);
        Route::post('stock-transfers/{stock_transfer}/approve', [StockTransferController::class, 'approve']);
        Route::post('stock-transfers/{stock_transfer}/ship', [StockTransferController::class, 'ship']);
        Route::post('stock-transfers/{stock_transfer}/receive', [StockTransferController::class, 'receive']);
        Route::post('stock-transfers/{stock_transfer}/complete', [StockTransferController::class, 'complete']);

        Route::apiResource('inventory-counts', InventoryCountController::class)->except(['update', 'destroy']);
        Route::post('inventory-counts/{inventory_count}/start', [InventoryCountController::class, 'start']);
        Route::post('inventory-counts/{inventory_count}/finalize', [InventoryCountController::class, 'finalize']);

        Route::prefix('dashboard')->group(function () {
            Route::get('summary', [DashboardController::class, 'summary']);
            Route::get('arrivals-today', [DashboardController::class, 'arrivalsToday']);
            Route::get('recent-activity', [DashboardController::class, 'recentActivity']);
            Route::get('warehouse-stats', [DashboardController::class, 'warehouseStats']);
            Route::get('employee-activity', [DashboardController::class, 'employeeActivity']);
        });

        Route::get('search', SearchController::class);
        Route::get('reports/{type}', ReportController::class);

        Route::get('notifications', [NotificationController::class, 'index']);
        Route::post('notifications/{id}/read', [NotificationController::class, 'markRead']);
        Route::post('notifications/read-all', [NotificationController::class, 'markAllRead']);

        Route::apiResource('audit-logs', AuditLogController::class)->only(['index', 'show']);
    });
});
PHP);

track('bootstrap/app.php', <<<'PHP'
<?php

use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        apiPrefix: 'api',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->api(prepend: [
            SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
PHP);

track('bootstrap/providers.php', <<<'PHP'
<?php

use App\Providers\AppServiceProvider;
use App\Providers\EventServiceProvider;

return [
    AppServiceProvider::class,
    EventServiceProvider::class,
];
PHP);

track('app/Providers/AppServiceProvider.php', <<<'PHP'
<?php

namespace App\Providers;

use App\Models\AuditLog;
use App\Models\Employee;
use App\Models\GoodsReceipt;
use App\Models\InventoryAdjustment;
use App\Models\InventoryCount;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\PurchaseOrder;
use App\Models\StockLevel;
use App\Models\StockTransfer;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Policies\AuditLogPolicy;
use App\Policies\EmployeePolicy;
use App\Policies\GoodsReceiptPolicy;
use App\Policies\InventoryAdjustmentPolicy;
use App\Policies\InventoryCountPolicy;
use App\Policies\InventoryTransactionPolicy;
use App\Policies\ProductCategoryPolicy;
use App\Policies\ProductPolicy;
use App\Policies\PurchaseOrderPolicy;
use App\Policies\StockLevelPolicy;
use App\Policies\StockTransferPolicy;
use App\Policies\SupplierPolicy;
use App\Policies\UserPolicy;
use App\Policies\WarehousePolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

class AppServiceProvider extends ServiceProvider
{
    protected $policies = [
        User::class => UserPolicy::class,
        Employee::class => EmployeePolicy::class,
        Supplier::class => SupplierPolicy::class,
        Warehouse::class => WarehousePolicy::class,
        ProductCategory::class => ProductCategoryPolicy::class,
        Product::class => ProductPolicy::class,
        InventoryTransaction::class => InventoryTransactionPolicy::class,
        StockLevel::class => StockLevelPolicy::class,
        PurchaseOrder::class => PurchaseOrderPolicy::class,
        GoodsReceipt::class => GoodsReceiptPolicy::class,
        InventoryAdjustment::class => InventoryAdjustmentPolicy::class,
        StockTransfer::class => StockTransferPolicy::class,
        InventoryCount::class => InventoryCountPolicy::class,
        AuditLog::class => AuditLogPolicy::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }

        Sanctum::usePersonalAccessTokenModel(\Laravel\Sanctum\PersonalAccessToken::class);
    }
}
PHP);

track('app/Models/User.php', <<<'PHP'
<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'locale',
        'theme',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function employee(): HasOne
    {
        return $this->hasOne(Employee::class);
    }
}
PHP);

track('app/Http/Controllers/Api/V1/ApiController.php', <<<'PHP'
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;

abstract class ApiController extends Controller
{
    use AuthorizesRequests;

    protected function success(mixed $data = null, ?string $message = 'OK', int $status = 200): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    protected function error(string $message, int $status = 400, ?array $errors = null, ?string $code = null): JsonResponse
    {
        return response()->json(array_filter([
            'message' => $message,
            'errors' => $errors,
            'code' => $code,
        ]), $status);
    }
}
PHP);

track('database/seeders/RolesAndPermissionsSeeder.php', <<<'PHP'
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'users.view', 'users.create', 'users.update', 'users.delete',
            'roles.view', 'roles.create', 'roles.update', 'roles.delete',
            'employees.view', 'employees.create', 'employees.update', 'employees.delete',
            'suppliers.view', 'suppliers.create', 'suppliers.update', 'suppliers.delete',
            'warehouses.view', 'warehouses.create', 'warehouses.update', 'warehouses.delete',
            'products.view', 'products.create', 'products.update', 'products.delete',
            'categories.view', 'categories.create', 'categories.update', 'categories.delete',
            'inventory.view', 'inventory.adjust',
            'inventory-adjustments.view', 'inventory-adjustments.create', 'inventory-adjustments.update', 'inventory-adjustments.delete', 'inventory-adjustments.approve',
            'inventory.transfer.view', 'inventory.transfer.create', 'inventory.transfer.approve', 'inventory.transfer.ship', 'inventory.transfer.receive',
            'stock-transfers.view', 'stock-transfers.create', 'stock-transfers.update', 'stock-transfers.delete',
            'purchase-orders.view', 'purchase-orders.create', 'purchase-orders.update', 'purchase-orders.delete', 'purchase-orders.send', 'purchase-orders.close',
            'goods-receipts.view', 'goods-receipts.create', 'goods-receipts.update', 'goods-receipts.confirm',
            'inventory-counts.view', 'inventory-counts.create', 'inventory-counts.update', 'inventory-counts.start', 'inventory-counts.finalize',
            'reports.view', 'reports.export',
            'audit.view',
            'notifications.manage',
            'settings.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $all = Permission::all();

        $roles = [
            'super-admin' => $all,
            'administrator' => $all->whereNotIn('name', ['settings.manage']),
            'warehouse-manager' => $all->where(fn ($p) => str_contains($p->name, 'warehouse') || str_contains($p->name, 'inventory') || str_contains($p->name, 'product') || str_contains($p->name, 'stock-transfers') || str_contains($p->name, 'inventory-counts') || str_contains($p->name, 'dashboard') || $p->name === 'reports.view'),
            'purchasing-manager' => $all->where(fn ($p) => str_contains($p->name, 'supplier') || str_contains($p->name, 'purchase-orders') || str_contains($p->name, 'goods-receipts') || $p->name === 'products.view' || $p->name === 'inventory.view' || $p->name === 'reports.view'),
            'warehouse-employee' => $all->where(fn ($p) => in_array($p->name, ['inventory.view', 'goods-receipts.view', 'goods-receipts.create', 'goods-receipts.confirm', 'stock-transfers.view', 'inventory.transfer.ship', 'inventory.transfer.receive', 'products.view', 'warehouses.view'], true)),
            'accountant' => $all->where(fn ($p) => str_contains($p->name, 'reports') || $p->name === 'inventory.view' || $p->name === 'audit.view' || $p->name === 'purchase-orders.view'),
            'viewer' => $all->where(fn ($p) => str_ends_with($p->name, '.view')),
        ];

        foreach ($roles as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($perms);
        }
    }
}
PHP);

track('database/seeders/DatabaseSeeder.php', <<<'PHP'
<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RolesAndPermissionsSeeder::class);

        $admin = User::factory()->create([
            'name' => 'WMS Admin',
            'email' => 'admin@wms.test',
            'password' => Hash::make('password'),
            'locale' => 'en',
        ]);
        $admin->assignRole('super-admin');

        User::factory()->create([
            'name' => 'Warehouse Manager',
            'email' => 'manager@wms.test',
            'password' => Hash::make('password'),
        ])->assignRole('warehouse-manager');

        Department::create(['name' => 'Operations', 'code' => 'OPS']);
        Supplier::factory(3)->create();
        Warehouse::factory(2)->create();
        ProductCategory::create(['name' => 'General']);
        Product::factory(10)->create();
    }
}
PHP);

// Factories
track('database/factories/ProductFactory.php', <<<'PHP'
<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(3, true),
            'sku' => strtoupper(fake()->unique()->bothify('SKU-####')),
            'barcode' => fake()->ean13(),
            'category_id' => ProductCategory::factory(),
            'supplier_id' => Supplier::factory(),
            'cost_price' => fake()->randomFloat(2, 1, 100),
            'sale_price' => fake()->randomFloat(2, 5, 200),
            'reorder_level' => fake()->numberBetween(5, 20),
        ];
    }
}
PHP);

track('database/factories/SupplierFactory.php', <<<'PHP'
<?php

namespace Database\Factories;

use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    protected $model = Supplier::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company(),
            'code' => strtoupper(fake()->unique()->bothify('SUP-###')),
            'email' => fake()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
        ];
    }
}
PHP);

track('database/factories/WarehouseFactory.php', <<<'PHP'
<?php

namespace Database\Factories;

use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseFactory extends Factory
{
    protected $model = Warehouse::class;

    public function definition(): array
    {
        return [
            'name' => fake()->city() . ' Warehouse',
            'code' => strtoupper(fake()->unique()->bothify('WH-##')),
            'address' => fake()->address(),
        ];
    }
}
PHP);

track('database/factories/PurchaseOrderFactory.php', <<<'PHP'
<?php

namespace Database\Factories;

use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class PurchaseOrderFactory extends Factory
{
    protected $model = PurchaseOrder::class;

    public function definition(): array
    {
        return [
            'number' => 'PO-' . fake()->unique()->numerify('######'),
            'supplier_id' => Supplier::factory(),
            'status' => 'draft',
            'expected_delivery_date' => fake()->dateTimeBetween('now', '+30 days'),
            'created_by' => User::factory(),
        ];
    }
}
PHP);

track('database/factories/ProductCategoryFactory.php', <<<'PHP'
<?php

namespace Database\Factories;

use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductCategoryFactory extends Factory
{
    protected $model = ProductCategory::class;

    public function definition(): array
    {
        return ['name' => fake()->word()];
    }
}
PHP);

track('database/factories/DepartmentFactory.php', <<<'PHP'
<?php

namespace Database\Factories;

use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

class DepartmentFactory extends Factory
{
    protected $model = Department::class;

    public function definition(): array
    {
        return [
            'name' => fake()->jobTitle(),
            'code' => strtoupper(fake()->unique()->lexify('???')),
        ];
    }
}
PHP);

// Lang files
foreach (['en', 'de', 'bs'] as $locale) {
    track("lang/{$locale}/auth.php", <<<PHP
<?php

return [
    'failed' => 'These credentials do not match our records.',
    'password' => 'The provided password is incorrect.',
    'throttle' => 'Too many login attempts. Please try again in :seconds seconds.',
    'login_success' => 'Login successful.',
    'logout_success' => 'Logged out successfully.',
    'profile_updated' => 'Profile updated successfully.',
    'password_changed' => 'Password changed successfully.',
];
PHP);
}

track('lang/en/inventory.php', <<<'PHP'
<?php

return ['insufficient_stock' => 'Insufficient stock for this movement.'];
PHP);

track('tests/Pest.php', <<<'PHP'
<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature', 'Unit');
PHP);

track('tests/Feature/AuthTest.php', <<<'PHP'
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
PHP);

track('tests/Feature/InventoryServiceTest.php', <<<'PHP'
<?php

use App\Models\Product;
use App\Models\StockLevel;
use App\Models\Warehouse;
use App\Services\InventoryService;

it('records a stock movement via API service layer', function () {
    $warehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();

    StockLevel::create([
        'warehouse_id' => $warehouse->id,
        'product_id' => $product->id,
        'quantity' => 100,
        'reserved_quantity' => 0,
    ]);

    $service = app(InventoryService::class);
    $transaction = $service->recordMovement([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'quantity' => -10,
        'type' => 'manual_correction',
    ]);

    expect($transaction->quantity)->toBe(-10);
    expect(StockLevel::first()->quantity)->toBe(90);
});
PHP);

track('tests/Unit/InventoryServiceTest.php', <<<'PHP'
<?php

use App\Models\Product;
use App\Models\StockLevel;
use App\Models\Warehouse;
use App\Services\InventoryService;
use Illuminate\Validation\ValidationException;

it('prevents negative stock levels', function () {
    $warehouse = Warehouse::factory()->create();
    $product = Product::factory()->create();

    StockLevel::create([
        'warehouse_id' => $warehouse->id,
        'product_id' => $product->id,
        'quantity' => 5,
        'reserved_quantity' => 0,
    ]);

    $service = app(InventoryService::class);

    $service->recordMovement([
        'product_id' => $product->id,
        'warehouse_id' => $warehouse->id,
        'quantity' => -10,
        'type' => 'manual_correction',
    ]);
})->throws(ValidationException::class);
PHP);

track('resources/views/reports/generic.blade.php', <<<'BLADE'
<!DOCTYPE html>
<html>
<head><title>{{ $type }} Report</title></head>
<body>
<h1>{{ ucfirst(str_replace('-', ' ', $type)) }} Report</h1>
<p>Generated at {{ now() }}</p>
</body>
</html>
BLADE);

file_put_contents($base . '/generated-files-part5.json', json_encode($created, JSON_PRETTY_PRINT));
echo 'Part 5: ' . count($created) . " files\n";
