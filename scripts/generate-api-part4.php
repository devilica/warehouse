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

// Form Requests
$requestMap = [
    'Auth/LoginRequest' => "public function rules(): array { return ['email' => 'required|email', 'password' => 'required|string', 'device_name' => 'nullable|string']; }",
    'Auth/UpdateProfileRequest' => "public function rules(): array { return ['name' => 'sometimes|string|max:255', 'email' => 'sometimes|email|unique:users,email,' . \$this->user()->id, 'locale' => 'sometimes|in:en,de,bs', 'theme' => 'sometimes|in:light,dark']; }",
    'Auth/ChangePasswordRequest' => "public function rules(): array { return ['current_password' => 'required|string', 'password' => 'required|string|min:8|confirmed']; }",
    'User/StoreUserRequest' => "public function rules(): array { return ['name' => 'required|string', 'email' => 'required|email|unique:users', 'password' => 'required|string|min:8', 'role' => 'nullable|string']; }",
    'User/UpdateUserRequest' => "public function rules(): array { return ['name' => 'sometimes|string', 'email' => 'sometimes|email|unique:users,email,' . \$this->route('user'), 'password' => 'nullable|string|min:8', 'role' => 'nullable|string']; }",
    'Employee/StoreEmployeeRequest' => "public function rules(): array { return ['first_name' => 'required|string', 'last_name' => 'required|string', 'department_id' => 'nullable|exists:departments,id', 'phone' => 'nullable|string']; }",
    'Employee/UpdateEmployeeRequest' => "public function rules(): array { return ['first_name' => 'sometimes|string', 'last_name' => 'sometimes|string', 'department_id' => 'nullable|exists:departments,id']; }",
    'Supplier/StoreSupplierRequest' => "public function rules(): array { return ['name' => 'required|string', 'code' => 'required|string|unique:suppliers', 'email' => 'nullable|email']; }",
    'Supplier/UpdateSupplierRequest' => "public function rules(): array { return ['name' => 'sometimes|string', 'code' => 'sometimes|string|unique:suppliers,code,' . \$this->route('supplier'), 'email' => 'nullable|email']; }",
    'Warehouse/StoreWarehouseRequest' => "public function rules(): array { return ['name' => 'required|string', 'code' => 'required|string|unique:warehouses']; }",
    'Warehouse/UpdateWarehouseRequest' => "public function rules(): array { return ['name' => 'sometimes|string', 'code' => 'sometimes|string|unique:warehouses,code,' . \$this->route('warehouse')]; }",
    'Product/StoreProductRequest' => "public function rules(): array { return ['name' => 'required|string', 'sku' => 'required|string|unique:products', 'category_id' => 'nullable|exists:product_categories,id', 'supplier_id' => 'nullable|exists:suppliers,id']; }",
    'Product/UpdateProductRequest' => "public function rules(): array { return ['name' => 'sometimes|string', 'sku' => 'sometimes|string|unique:products,sku,' . \$this->route('product')]; }",
    'PurchaseOrder/StorePurchaseOrderRequest' => "public function rules(): array { return ['supplier_id' => 'required|exists:suppliers,id', 'expected_delivery_date' => 'nullable|date', 'items' => 'required|array|min:1', 'items.*.product_id' => 'required|exists:products,id', 'items.*.quantity' => 'required|integer|min:1']; }",
    'GoodsReceipt/StoreGoodsReceiptRequest' => "public function rules(): array { return ['purchase_order_id' => 'required|exists:purchase_orders,id', 'warehouse_id' => 'required|exists:warehouses,id']; }",
    'InventoryAdjustment/StoreInventoryAdjustmentRequest' => "public function rules(): array { return ['warehouse_id' => 'required|exists:warehouses,id', 'reason' => 'nullable|string', 'items' => 'required|array|min:1']; }",
    'StockTransfer/StoreStockTransferRequest' => "public function rules(): array { return ['from_warehouse_id' => 'required|exists:warehouses,id', 'to_warehouse_id' => 'required|exists:warehouses,id|different:from_warehouse_id', 'items' => 'required|array|min:1']; }",
    'InventoryCount/StoreInventoryCountRequest' => "public function rules(): array { return ['warehouse_id' => 'required|exists:warehouses,id', 'type' => 'required|in:full,partial', 'scheduled_at' => 'nullable|date']; }",
];

foreach ($requestMap as $name => $rules) {
    [$folder, $class] = explode('/', $name);
    track("app/Http/Requests/{$folder}/{$class}.php", <<<PHP
<?php

namespace App\Http\Requests\\{$folder};

use Illuminate\Foundation\Http\FormRequest;

class {$class} extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    {$rules}
}
PHP);
}

// Controllers
track('app/Http/Controllers/Api/V1/AuthController.php', <<<'PHP'
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\UpdateProfileRequest;
use App\Http\Resources\AuthUserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends ApiController
{
    public function __construct(private AuthService $authService) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $result = $this->authService->login(
            $request->email,
            $request->password,
            $request->device_name ?? 'api'
        );

        return $this->success([
            'token' => $result['token'],
            'user' => new AuthUserResource($result['user']),
        ], __('auth.login_success'));
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->success(null, __('auth.logout_success'));
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success(new AuthUserResource($request->user()->load(['roles', 'permissions', 'employee'])));
    }

    public function updateProfile(UpdateProfileRequest $request): JsonResponse
    {
        $user = $this->authService->updateProfile($request->user(), $request->validated());

        return $this->success(new AuthUserResource($user), __('auth.profile_updated'));
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $this->authService->changePassword($request->user(), $request->current_password, $request->password);

        return $this->success(null, __('auth.password_changed'));
    }
}
PHP);

$crudControllers = [
    'User' => ['model' => 'User', 'resource' => 'UserResource', 'store' => 'StoreUserRequest', 'update' => 'UpdateUserRequest', 'policy' => 'User', 'filters' => "AllowedFilter::partial('name'), AllowedFilter::partial('email')", 'includes' => "AllowedInclude::relationship('roles')"],
    'Employee' => ['model' => 'Employee', 'resource' => 'EmployeeResource', 'store' => 'StoreEmployeeRequest', 'update' => 'UpdateEmployeeRequest', 'policy' => 'Employee', 'filters' => "AllowedFilter::partial('first_name'), AllowedFilter::partial('last_name')", 'includes' => "AllowedInclude::relationship('department'), AllowedInclude::relationship('user')"],
    'Supplier' => ['model' => 'Supplier', 'resource' => 'SupplierResource', 'store' => 'StoreSupplierRequest', 'update' => 'UpdateSupplierRequest', 'policy' => 'Supplier', 'filters' => "AllowedFilter::partial('name'), AllowedFilter::exact('code')", 'includes' => "AllowedInclude::relationship('contacts')"],
    'Warehouse' => ['model' => 'Warehouse', 'resource' => 'WarehouseResource', 'store' => 'StoreWarehouseRequest', 'update' => 'UpdateWarehouseRequest', 'policy' => 'Warehouse', 'filters' => "AllowedFilter::partial('name')", 'includes' => "AllowedInclude::relationship('zones')"],
    'ProductCategory' => ['model' => 'ProductCategory', 'resource' => 'ProductCategoryResource', 'store' => null, 'update' => null, 'policy' => 'ProductCategory', 'filters' => "AllowedFilter::partial('name')", 'includes' => "AllowedInclude::relationship('products')", 'folder' => 'ProductCategory'],
    'Product' => ['model' => 'Product', 'resource' => 'ProductResource', 'store' => 'StoreProductRequest', 'update' => 'UpdateProductRequest', 'policy' => 'Product', 'filters' => "AllowedFilter::partial('name'), AllowedFilter::partial('sku')", 'includes' => "AllowedInclude::relationship('category'), AllowedInclude::relationship('supplier'), AllowedInclude::relationship('barcodes')"],
    'InventoryTransaction' => ['model' => 'InventoryTransaction', 'resource' => 'InventoryTransactionResource', 'store' => null, 'update' => null, 'policy' => 'InventoryTransaction', 'filters' => "AllowedFilter::exact('type'), AllowedFilter::exact('warehouse_id')", 'includes' => "AllowedInclude::relationship('product'), AllowedInclude::relationship('warehouse')", 'readonly' => true],
    'StockLevel' => ['model' => 'StockLevel', 'resource' => 'StockLevelResource', 'store' => null, 'update' => null, 'policy' => 'StockLevel', 'filters' => "AllowedFilter::exact('warehouse_id'), AllowedFilter::exact('product_id')", 'includes' => "AllowedInclude::relationship('product'), AllowedInclude::relationship('warehouse')", 'readonly' => true],
    'AuditLog' => ['model' => 'AuditLog', 'resource' => 'AuditLogResource', 'store' => null, 'update' => null, 'policy' => 'AuditLog', 'filters' => "AllowedFilter::exact('action')", 'includes' => "AllowedInclude::relationship('user')", 'readonly' => true, 'noCreate' => true],
];

foreach ($crudControllers as $controller => $cfg) {
    $folder = $cfg['folder'] ?? $controller;
    $model = $cfg['model'];
    $resource = $cfg['resource'];
    $policy = $cfg['policy'];
    $param = lcfirst($controller === 'ProductCategory' ? 'category' : ($controller === 'InventoryTransaction' ? 'inventoryTransaction' : ($controller === 'StockLevel' ? 'stockLevel' : ($controller === 'AuditLog' ? 'auditLog' : $controller))));

    $storeMethod = '';
    if (empty($cfg['readonly']) && $cfg['store']) {
        $storeMethod = <<<PHP

    public function store({$folder}\\{$cfg['store']} \$request): JsonResponse
    {
        \$this->authorize('create', {$model}::class);
        \$model = {$model}::create(\$request->validated());
        return \$this->success(new {$resource}(\$model), null, 201);
    }
PHP;
    }

    $updateMethod = '';
    if (empty($cfg['readonly']) && $cfg['update']) {
        $updateMethod = <<<PHP

    public function update({$folder}\\{$cfg['update']} \$request, {$model} \${$param}): JsonResponse
    {
        \$this->authorize('update', \${$param});
        \${$param}->update(\$request->validated());
        return \$this->success(new {$resource}(\${$param}->fresh()));
    }
PHP;
    }

    $destroyMethod = empty($cfg['readonly']) && empty($cfg['noCreate']) ? <<<PHP

    public function destroy({$model} \${$param}): JsonResponse
    {
        \$this->authorize('delete', \${$param});
        \${$param}->delete();
        return \$this->success(null, null, 204);
    }
PHP : '';

    track("app/Http/Controllers/Api/V1/{$controller}Controller.php", <<<PHP
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\\{$resource};
use App\Models\\{$model};
use Illuminate\Http\JsonResponse;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;

class {$controller}Controller extends ApiController
{
    public function index(): JsonResponse
    {
        \$this->authorize('viewAny', {$model}::class);
        \$items = QueryBuilder::for({$model}::class)
            ->allowedFilters([{$cfg['filters']}])
            ->allowedIncludes([{$cfg['includes']}])
            ->allowedSorts(['created_at', 'id'])
            ->paginate(request('per_page', 15));

        return \$this->success({$resource}::collection(\$items));
    }

    public function show({$model} \${$param}): JsonResponse
    {
        \$this->authorize('view', \${$param});
        return \$this->success(new {$resource}(\${$param}->loadMissing([])));
    }
    {$storeMethod}
    {$updateMethod}
    {$destroyMethod}
}
PHP);
}

// Workflow controllers
track('app/Http/Controllers/Api/V1/PurchaseOrderController.php', <<<'PHP'
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\PurchaseOrder\StorePurchaseOrderRequest;
use App\Http\Resources\PurchaseOrderResource;
use App\Models\PurchaseOrder;
use App\Services\PurchaseOrderService;
use Illuminate\Http\JsonResponse;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;

class PurchaseOrderController extends ApiController
{
    public function __construct(private PurchaseOrderService $service) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', PurchaseOrder::class);
        $items = QueryBuilder::for(PurchaseOrder::class)
            ->allowedFilters([AllowedFilter::exact('status'), AllowedFilter::exact('supplier_id')])
            ->allowedIncludes([AllowedInclude::relationship('supplier'), AllowedInclude::relationship('items')])
            ->paginate(request('per_page', 15));

        return $this->success(PurchaseOrderResource::collection($items));
    }

    public function show(PurchaseOrder $purchaseOrder): JsonResponse
    {
        $this->authorize('view', $purchaseOrder);
        return $this->success(new PurchaseOrderResource($purchaseOrder->load(['supplier', 'items.product'])));
    }

    public function store(StorePurchaseOrderRequest $request): JsonResponse
    {
        $this->authorize('create', PurchaseOrder::class);
        $order = $this->service->create($request->validated());
        return $this->success(new PurchaseOrderResource($order), null, 201);
    }

    public function send(PurchaseOrder $purchaseOrder): JsonResponse
    {
        $this->authorize('update', $purchaseOrder);
        return $this->success(new PurchaseOrderResource($this->service->send($purchaseOrder)));
    }

    public function close(PurchaseOrder $purchaseOrder): JsonResponse
    {
        $this->authorize('update', $purchaseOrder);
        return $this->success(new PurchaseOrderResource($this->service->close($purchaseOrder)));
    }
}
PHP);

track('app/Http/Controllers/Api/V1/GoodsReceiptController.php', <<<'PHP'
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\GoodsReceipt\StoreGoodsReceiptRequest;
use App\Http\Resources\GoodsReceiptResource;
use App\Models\GoodsReceipt;
use App\Services\GoodsReceiptService;
use Illuminate\Http\JsonResponse;
use Spatie\QueryBuilder\QueryBuilder;

class GoodsReceiptController extends ApiController
{
    public function __construct(private GoodsReceiptService $service) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', GoodsReceipt::class);
        return $this->success(GoodsReceiptResource::collection(QueryBuilder::for(GoodsReceipt::class)->paginate()));
    }

    public function show(GoodsReceipt $goodsReceipt): JsonResponse
    {
        $this->authorize('view', $goodsReceipt);
        return $this->success(new GoodsReceiptResource($goodsReceipt->load('items.product')));
    }

    public function store(StoreGoodsReceiptRequest $request): JsonResponse
    {
        $this->authorize('create', GoodsReceipt::class);
        $receipt = GoodsReceipt::create($request->validated() + ['status' => 'draft']);
        return $this->success(new GoodsReceiptResource($receipt), null, 201);
    }

    public function confirm(GoodsReceipt $goodsReceipt): JsonResponse
    {
        $this->authorize('update', $goodsReceipt);
        return $this->success(new GoodsReceiptResource($this->service->confirm($goodsReceipt)));
    }
}
PHP);

track('app/Http/Controllers/Api/V1/InventoryAdjustmentController.php', <<<'PHP'
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\InventoryAdjustment\StoreInventoryAdjustmentRequest;
use App\Http\Resources\InventoryAdjustmentResource;
use App\Models\InventoryAdjustment;
use App\Services\InventoryAdjustmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;

class InventoryAdjustmentController extends ApiController
{
    public function __construct(private InventoryAdjustmentService $service) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', InventoryAdjustment::class);
        return $this->success(InventoryAdjustmentResource::collection(QueryBuilder::for(InventoryAdjustment::class)->paginate()));
    }

    public function show(InventoryAdjustment $inventoryAdjustment): JsonResponse
    {
        $this->authorize('view', $inventoryAdjustment);
        return $this->success(new InventoryAdjustmentResource($inventoryAdjustment->load('items.product')));
    }

    public function store(StoreInventoryAdjustmentRequest $request): JsonResponse
    {
        $this->authorize('create', InventoryAdjustment::class);
        $adjustment = DB::transaction(function () use ($request) {
            $data = $request->validated();
            $items = $data['items'];
            unset($data['items']);
            $adj = InventoryAdjustment::create($data + ['status' => 'pending']);
            foreach ($items as $item) {
                $adj->items()->create($item);
            }
            return $adj->load('items.product');
        });
        return $this->success(new InventoryAdjustmentResource($adjustment), null, 201);
    }

    public function approve(InventoryAdjustment $inventoryAdjustment): JsonResponse
    {
        $this->authorize('update', $inventoryAdjustment);
        return $this->success(new InventoryAdjustmentResource($this->service->approve($inventoryAdjustment)));
    }
}
PHP);

track('app/Http/Controllers/Api/V1/StockTransferController.php', <<<'PHP'
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\StockTransfer\StoreStockTransferRequest;
use App\Http\Resources\StockTransferResource;
use App\Models\StockTransfer;
use App\Services\StockTransferService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;

class StockTransferController extends ApiController
{
    public function __construct(private StockTransferService $service) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', StockTransfer::class);
        return $this->success(StockTransferResource::collection(QueryBuilder::for(StockTransfer::class)->paginate()));
    }

    public function show(StockTransfer $stockTransfer): JsonResponse
    {
        $this->authorize('view', $stockTransfer);
        return $this->success(new StockTransferResource($stockTransfer->load('items.product')));
    }

    public function store(StoreStockTransferRequest $request): JsonResponse
    {
        $this->authorize('create', StockTransfer::class);
        $transfer = DB::transaction(function () use ($request) {
            $data = $request->validated();
            $items = $data['items'];
            unset($data['items']);
            $t = StockTransfer::create($data + ['status' => 'draft']);
            foreach ($items as $item) {
                $t->items()->create($item);
            }
            return $t->load('items.product');
        });
        return $this->success(new StockTransferResource($transfer), null, 201);
    }

    public function approve(StockTransfer $stockTransfer): JsonResponse
    {
        $this->authorize('update', $stockTransfer);
        return $this->success(new StockTransferResource($this->service->approve($stockTransfer)));
    }

    public function ship(StockTransfer $stockTransfer): JsonResponse
    {
        $this->authorize('update', $stockTransfer);
        return $this->success(new StockTransferResource($this->service->ship($stockTransfer)));
    }

    public function receive(StockTransfer $stockTransfer): JsonResponse
    {
        $this->authorize('update', $stockTransfer);
        return $this->success(new StockTransferResource($this->service->receive($stockTransfer)));
    }

    public function complete(StockTransfer $stockTransfer): JsonResponse
    {
        $this->authorize('update', $stockTransfer);
        return $this->success(new StockTransferResource($this->service->complete($stockTransfer)));
    }
}
PHP);

track('app/Http/Controllers/Api/V1/InventoryCountController.php', <<<'PHP'
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\InventoryCount\StoreInventoryCountRequest;
use App\Http\Resources\InventoryCountResource;
use App\Models\InventoryCount;
use App\Services\InventoryCountService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\QueryBuilder\QueryBuilder;

class InventoryCountController extends ApiController
{
    public function __construct(private InventoryCountService $service) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', InventoryCount::class);
        return $this->success(InventoryCountResource::collection(QueryBuilder::for(InventoryCount::class)->paginate()));
    }

    public function show(InventoryCount $inventoryCount): JsonResponse
    {
        $this->authorize('view', $inventoryCount);
        return $this->success(new InventoryCountResource($inventoryCount->load('items.product')));
    }

    public function store(StoreInventoryCountRequest $request): JsonResponse
    {
        $this->authorize('create', InventoryCount::class);
        $count = InventoryCount::create($request->validated() + ['status' => 'scheduled']);
        return $this->success(new InventoryCountResource($count), null, 201);
    }

    public function start(InventoryCount $inventoryCount): JsonResponse
    {
        $this->authorize('update', $inventoryCount);
        return $this->success(new InventoryCountResource($this->service->start($inventoryCount)));
    }

    public function finalize(Request $request, InventoryCount $inventoryCount): JsonResponse
    {
        $this->authorize('update', $inventoryCount);
        return $this->success(new InventoryCountResource(
            $this->service->finalize($inventoryCount, $request->boolean('auto_adjust'))
        ));
    }
}
PHP);

track('app/Http/Controllers/Api/V1/WarehouseZoneController.php', <<<'PHP'
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\WarehouseZoneResource;
use App\Models\Warehouse;
use App\Models\WarehouseZone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WarehouseZoneController extends ApiController
{
    public function index(Warehouse $warehouse): JsonResponse
    {
        $this->authorize('view', $warehouse);
        return $this->success(WarehouseZoneResource::collection($warehouse->zones()->paginate()));
    }

    public function store(Request $request, Warehouse $warehouse): JsonResponse
    {
        $this->authorize('update', $warehouse);
        $zone = $warehouse->zones()->create($request->validate(['name' => 'required|string', 'code' => 'required|string']));
        return $this->success(new WarehouseZoneResource($zone), null, 201);
    }

    public function show(Warehouse $warehouse, WarehouseZone $zone): JsonResponse
    {
        $this->authorize('view', $warehouse);
        return $this->success(new WarehouseZoneResource($zone->load('shelves')));
    }

    public function update(Request $request, Warehouse $warehouse, WarehouseZone $zone): JsonResponse
    {
        $this->authorize('update', $warehouse);
        $zone->update($request->validate(['name' => 'sometimes|string', 'code' => 'sometimes|string']));
        return $this->success(new WarehouseZoneResource($zone));
    }

    public function destroy(Warehouse $warehouse, WarehouseZone $zone): JsonResponse
    {
        $this->authorize('update', $warehouse);
        $zone->delete();
        return $this->success(null, null, 204);
    }
}
PHP);

track('app/Http/Controllers/Api/V1/WarehouseShelfController.php', <<<'PHP'
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\WarehouseShelfResource;
use App\Models\Warehouse;
use App\Models\WarehouseShelf;
use App\Models\WarehouseZone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WarehouseShelfController extends ApiController
{
    public function index(Warehouse $warehouse, WarehouseZone $zone): JsonResponse
    {
        $this->authorize('view', $warehouse);
        return $this->success(WarehouseShelfResource::collection($zone->shelves()->paginate()));
    }

    public function store(Request $request, Warehouse $warehouse, WarehouseZone $zone): JsonResponse
    {
        $this->authorize('update', $warehouse);
        $shelf = $zone->shelves()->create($request->validate(['name' => 'required|string', 'code' => 'required|string']));
        return $this->success(new WarehouseShelfResource($shelf), null, 201);
    }

    public function update(Request $request, Warehouse $warehouse, WarehouseZone $zone, WarehouseShelf $shelf): JsonResponse
    {
        $this->authorize('update', $warehouse);
        $shelf->update($request->validate(['name' => 'sometimes|string', 'code' => 'sometimes|string']));
        return $this->success(new WarehouseShelfResource($shelf));
    }

    public function destroy(Warehouse $warehouse, WarehouseZone $zone, WarehouseShelf $shelf): JsonResponse
    {
        $this->authorize('update', $warehouse);
        $shelf->delete();
        return $this->success(null, null, 204);
    }
}
PHP);

track('app/Http/Controllers/Api/V1/WarehouseLocationController.php', <<<'PHP'
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Resources\WarehouseLocationResource;
use App\Models\Warehouse;
use App\Models\WarehouseLocation;
use App\Models\WarehouseShelf;
use App\Models\WarehouseZone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WarehouseLocationController extends ApiController
{
    public function index(Warehouse $warehouse, WarehouseZone $zone, WarehouseShelf $shelf): JsonResponse
    {
        $this->authorize('view', $warehouse);
        return $this->success(WarehouseLocationResource::collection($shelf->locations()->paginate()));
    }

    public function store(Request $request, Warehouse $warehouse, WarehouseZone $zone, WarehouseShelf $shelf): JsonResponse
    {
        $this->authorize('update', $warehouse);
        $location = $shelf->locations()->create($request->validate(['code' => 'required|string', 'barcode' => 'nullable|string']));
        return $this->success(new WarehouseLocationResource($location), null, 201);
    }

    public function update(Request $request, Warehouse $warehouse, WarehouseZone $zone, WarehouseShelf $shelf, WarehouseLocation $location): JsonResponse
    {
        $this->authorize('update', $warehouse);
        $location->update($request->validate(['code' => 'sometimes|string', 'barcode' => 'nullable|string']));
        return $this->success(new WarehouseLocationResource($location));
    }

    public function destroy(Warehouse $warehouse, WarehouseZone $zone, WarehouseShelf $shelf, WarehouseLocation $location): JsonResponse
    {
        $this->authorize('update', $warehouse);
        $location->delete();
        return $this->success(null, null, 204);
    }
}
PHP);

track('app/Http/Controllers/Api/V1/ProductController.php', <<<'PHP'
<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\BarcodeService;
use Illuminate\Http\JsonResponse;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;

class ProductController extends ApiController
{
    public function __construct(private BarcodeService $barcodeService) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', Product::class);
        $items = QueryBuilder::for(Product::class)
            ->allowedFilters([AllowedFilter::partial('name'), AllowedFilter::partial('sku')])
            ->allowedIncludes([AllowedInclude::relationship('category'), AllowedInclude::relationship('barcodes')])
            ->paginate(request('per_page', 15));
        return $this->success(ProductResource::collection($items));
    }

    public function show(Product $product): JsonResponse
    {
        $this->authorize('view', $product);
        return $this->success(new ProductResource($product->load(['category', 'barcodes'])));
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $this->authorize('create', Product::class);
        $product = Product::create($request->validated());
        return $this->success(new ProductResource($product), null, 201);
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $this->authorize('update', $product);
        $product->update($request->validated());
        return $this->success(new ProductResource($product));
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->authorize('delete', $product);
        $product->delete();
        return $this->success(null, null, 204);
    }

    public function byBarcode(string $code): JsonResponse
    {
        $product = $this->barcodeService->findByBarcode($code);
        if (! $product) {
            return $this->error(__('products.not_found'), 404, code: 'NOT_FOUND');
        }
        $this->authorize('view', $product);
        return $this->success(new ProductResource($product->load('barcodes')));
    }

    public function generateBarcode(Product $product): JsonResponse
    {
        $this->authorize('update', $product);
        $barcode = $this->barcodeService->generate($product);
        return $this->success($barcode, null, 201);
    }
}
PHP);

track('app/Http/Controllers/Api/V1/DashboardController.php', <<<'PHP'
<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;

class DashboardController extends ApiController
{
    public function __construct(private DashboardService $dashboardService) {}

    public function summary(): JsonResponse
    {
        return $this->success($this->dashboardService->summary());
    }

    public function arrivalsToday(): JsonResponse
    {
        return $this->success($this->dashboardService->arrivalsToday());
    }

    public function recentActivity(): JsonResponse
    {
        return $this->success($this->dashboardService->recentActivity());
    }

    public function warehouseStats(): JsonResponse
    {
        return $this->success($this->dashboardService->warehouseStats());
    }

    public function employeeActivity(): JsonResponse
    {
        return $this->success($this->dashboardService->employeeActivity());
    }
}
PHP);

track('app/Http/Controllers/Api/V1/SearchController.php', <<<'PHP'
<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends ApiController
{
    public function __construct(private SearchService $searchService) {}

    public function __invoke(Request $request): JsonResponse
    {
        $request->validate(['q' => 'required|string|min:2']);
        return $this->success($this->searchService->search($request->q));
    }
}
PHP);

track('app/Http/Controllers/Api/V1/ReportController.php', <<<'PHP'
<?php

namespace App\Http\Controllers\Api\V1;

use App\Services\ReportService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ReportController extends ApiController
{
    public function __construct(private ReportService $reportService) {}

    public function __invoke(Request $request, string $type): Response
    {
        $request->validate(['format' => 'nullable|in:pdf,xlsx,csv']);
        return $this->reportService->generate($type, $request->get('format', 'csv'));
    }
}
PHP);

track('app/Http/Controllers/Api/V1/NotificationController.php', <<<'PHP'
<?php

namespace App\Http\Controllers\Api\V1;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends ApiController
{
    public function index(Request $request): JsonResponse
    {
        return $this->success($request->user()->notifications()->paginate());
    }

    public function markRead(Request $request, string $id): JsonResponse
    {
        $notification = $request->user()->notifications()->findOrFail($id);
        $notification->markAsRead();
        return $this->success($notification);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->unreadNotifications->markAsRead();
        return $this->success(null, __('notifications.marked_read'));
    }
}
PHP);

file_put_contents($base . '/generated-files-part4.json', json_encode($created, JSON_PRETTY_PRINT));
echo 'Part 4: ' . count($created) . " files\n";
