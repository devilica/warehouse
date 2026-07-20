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
    echo "Created: {$path}\n";
}

$created = [];

function track(string $path, string $content): void
{
    global $created, $base;
    $full = $base . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $path);
    writeFile($full, $content);
    $created[] = $path;
}

// --- Middleware ---
track('app/Http/Middleware/SetLocale.php', <<<'PHP'
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->header('Accept-Language')
            ?? $request->user()?->locale
            ?? config('app.locale');

        if (in_array($locale, ['en', 'de', 'bs'], true)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
PHP);

// --- Base Api Controller ---
track('app/Http/Controllers/Api/V1/ApiController.php', <<<'PHP'
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

abstract class ApiController extends Controller
{
    protected function success(mixed $data = null, string $message = 'OK', int $status = 200): JsonResponse
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

// --- Policy trait ---
track('app/Policies/Concerns/ChecksPermissions.php', <<<'PHP'
<?php

namespace App\Policies\Concerns;

use App\Models\User;

trait ChecksPermissions
{
    protected function can(User $user, string $permission): bool
    {
        return $user->hasRole('super-admin') || $user->can($permission);
    }
}
PHP);

$policyModels = [
    'User' => 'users',
    'Employee' => 'employees',
    'Supplier' => 'suppliers',
    'Warehouse' => 'warehouses',
    'ProductCategory' => 'categories',
    'Product' => 'products',
    'PurchaseOrder' => 'purchase-orders',
    'GoodsReceipt' => 'goods-receipts',
    'InventoryAdjustment' => 'inventory-adjustments',
    'StockTransfer' => 'stock-transfers',
    'InventoryCount' => 'inventory-counts',
    'InventoryTransaction' => 'inventory',
    'StockLevel' => 'inventory',
    'AuditLog' => 'audit',
];

foreach ($policyModels as $model => $prefix) {
    $class = "{$model}Policy";
    $view = $prefix === 'inventory' ? 'inventory.view' : "{$prefix}.view";
    $create = $prefix === 'inventory' ? 'inventory.adjust' : "{$prefix}.create";
    $update = $prefix === 'inventory' ? 'inventory.adjust' : "{$prefix}.update";
    $delete = $prefix === 'audit' ? 'audit.view' : ($prefix === 'inventory' ? 'inventory.adjust' : "{$prefix}.delete");

    track("app/Policies/{$class}.php", <<<PHP
<?php

namespace App\Policies;

use App\Models\\{$model};
use App\Models\User;
use App\Policies\Concerns\ChecksPermissions;

class {$class}
{
    use ChecksPermissions;

    public function viewAny(User \$user): bool
    {
        return \$this->can(\$user, '{$view}');
    }

    public function view(User \$user, {$model} \$model): bool
    {
        return \$this->can(\$user, '{$view}');
    }

    public function create(User \$user): bool
    {
        return \$this->can(\$user, '{$create}');
    }

    public function update(User \$user, {$model} \$model): bool
    {
        return \$this->can(\$user, '{$update}');
    }

    public function delete(User \$user, {$model} \$model): bool
    {
        return \$this->can(\$user, '{$delete}');
    }
}
PHP);
}

// --- Events ---
$events = [
    'StockMovementRecorded' => ['product_id', 'warehouse_id', 'quantity', 'type'],
    'GoodsReceiptConfirmed' => ['goodsReceipt'],
    'LowStockDetected' => ['product_id', 'warehouse_id', 'current_quantity', 'reorder_level'],
    'StockTransferCompleted' => ['stockTransfer'],
];

foreach ($events as $event => $props) {
    $params = implode(', ', array_map(fn ($p) => "public mixed \${$p}", $props));
    $assign = implode("\n        ", array_map(fn ($p) => "\$this->{$p} = \${$p};", $props));
    track("app/Events/{$event}.php", <<<PHP
<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class {$event}
{
    use Dispatchable, SerializesModels;

    public function __construct({$params})
    {
        {$assign}
    }
}
PHP);
}

$listeners = [
    'UpdateDashboardCacheOnStockMovement' => ['StockMovementRecorded', 'handleStockMovementRecorded'],
    'CheckLowStockOnMovement' => ['StockMovementRecorded', 'handleStockMovementRecorded'],
    'NotifyPurchasingOnGoodsReceipt' => ['GoodsReceiptConfirmed', 'handleGoodsReceiptConfirmed'],
    'SendLowStockNotification' => ['LowStockDetected', 'handleLowStockDetected'],
    'NotifyWarehouseManagersOnTransfer' => ['StockTransferCompleted', 'handleStockTransferCompleted'],
];

foreach ($listeners as $listener => [$event, $method]) {
    track("app/Listeners/{$listener}.php", <<<PHP
<?php

namespace App\Listeners;

use App\Events\\{$event};
use App\Services\DashboardService;

class {$listener}
{
    public function __construct(private DashboardService \$dashboardService) {}

    public function {$method}({$event} \$event): void
    {
        // Hook for notifications / cache invalidation
        \$this->dashboardService->clearCache();
    }
}
PHP);
}

track('app/Providers/EventServiceProvider.php', <<<'PHP'
<?php

namespace App\Providers;

use App\Events\GoodsReceiptConfirmed;
use App\Events\LowStockDetected;
use App\Events\StockMovementRecorded;
use App\Events\StockTransferCompleted;
use App\Listeners\CheckLowStockOnMovement;
use App\Listeners\NotifyPurchasingOnGoodsReceipt;
use App\Listeners\NotifyWarehouseManagersOnTransfer;
use App\Listeners\SendLowStockNotification;
use App\Listeners\UpdateDashboardCacheOnStockMovement;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        StockMovementRecorded::class => [
            UpdateDashboardCacheOnStockMovement::class,
            CheckLowStockOnMovement::class,
        ],
        GoodsReceiptConfirmed::class => [
            NotifyPurchasingOnGoodsReceipt::class,
        ],
        LowStockDetected::class => [
            SendLowStockNotification::class,
        ],
        StockTransferCompleted::class => [
            NotifyWarehouseManagersOnTransfer::class,
        ],
    ];
}
PHP);

file_put_contents($base . '/generated-files.json', json_encode($created, JSON_PRETTY_PRINT));
echo "\nTotal tracked so far: " . count($created) . "\n";
