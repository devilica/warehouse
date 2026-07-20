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

$models = [
    'Employee' => ['user_id', 'department_id', 'first_name', 'last_name', 'phone', 'position'],
    'Department' => ['name', 'code'],
    'Supplier' => ['name', 'code', 'email', 'phone', 'address'],
    'SupplierContact' => ['supplier_id', 'name', 'email', 'phone'],
    'Warehouse' => ['name', 'code', 'address', 'manager_id'],
    'WarehouseZone' => ['warehouse_id', 'name', 'code'],
    'WarehouseShelf' => ['warehouse_zone_id', 'name', 'code'],
    'WarehouseLocation' => ['warehouse_shelf_id', 'code', 'barcode'],
    'ProductCategory' => ['name', 'parent_id'],
    'Product' => ['name', 'sku', 'barcode', 'category_id', 'supplier_id', 'cost_price', 'sale_price', 'reorder_level'],
    'ProductImage' => ['product_id', 'path'],
    'ProductDocument' => ['product_id', 'path', 'name'],
    'ProductBarcode' => ['product_id', 'code', 'type', 'image', 'is_primary'],
    'StockLevel' => ['product_id', 'warehouse_id', 'warehouse_location_id', 'quantity', 'reserved_quantity'],
    'InventoryTransaction' => ['product_id', 'warehouse_id', 'warehouse_location_id', 'user_id', 'type', 'quantity', 'reference_type', 'reference_id', 'notes'],
    'PurchaseOrder' => ['number', 'supplier_id', 'status', 'expected_delivery_date', 'created_by', 'sent_at', 'closed_at'],
    'PurchaseOrderItem' => ['purchase_order_id', 'product_id', 'quantity', 'unit_price'],
    'GoodsReceipt' => ['purchase_order_id', 'warehouse_id', 'status', 'confirmed_at', 'confirmed_by'],
    'GoodsReceiptItem' => ['goods_receipt_id', 'purchase_order_item_id', 'product_id', 'warehouse_location_id', 'quantity_received', 'quantity_damaged'],
    'InventoryAdjustment' => ['warehouse_id', 'status', 'reason', 'approved_at', 'approved_by'],
    'InventoryAdjustmentItem' => ['inventory_adjustment_id', 'product_id', 'warehouse_location_id', 'quantity_delta', 'reason'],
    'StockTransfer' => ['from_warehouse_id', 'to_warehouse_id', 'status', 'approved_at', 'approved_by', 'shipped_at', 'received_at', 'completed_at'],
    'StockTransferItem' => ['stock_transfer_id', 'product_id', 'from_warehouse_location_id', 'to_warehouse_location_id', 'quantity'],
    'InventoryCount' => ['warehouse_id', 'status', 'type', 'scheduled_at', 'started_at', 'finalized_at'],
    'InventoryCountItem' => ['inventory_count_id', 'product_id', 'warehouse_location_id', 'expected_quantity', 'counted_quantity'],
    'AuditLog' => ['user_id', 'auditable_type', 'auditable_id', 'action', 'old_values', 'new_values', 'ip_address', 'user_agent'],
    'MediaFile' => ['fileable_type', 'fileable_id', 'path', 'mime_type', 'size'],
];

foreach ($models as $model => $fillable) {
    $fillableStr = implode("', '", $fillable);
    $relations = match ($model) {
        'Employee' => "public function user() { return \$this->belongsTo(User::class); }\n    public function department() { return \$this->belongsTo(Department::class); }",
        'Supplier' => "public function contacts() { return \$this->hasMany(SupplierContact::class); }\n    public function products() { return \$this->hasMany(Product::class); }\n    public function purchaseOrders() { return \$this->hasMany(PurchaseOrder::class); }",
        'SupplierContact' => 'public function supplier() { return $this->belongsTo(Supplier::class); }',
        'Warehouse' => "public function zones() { return \$this->hasMany(WarehouseZone::class); }\n    public function stockLevels() { return \$this->hasMany(StockLevel::class); }",
        'WarehouseZone' => "public function warehouse() { return \$this->belongsTo(Warehouse::class); }\n    public function shelves() { return \$this->hasMany(WarehouseShelf::class); }",
        'WarehouseShelf' => "public function zone() { return \$this->belongsTo(WarehouseZone::class, 'warehouse_zone_id'); }\n    public function locations() { return \$this->hasMany(WarehouseLocation::class); }",
        'WarehouseLocation' => 'public function shelf() { return $this->belongsTo(WarehouseShelf::class, "warehouse_shelf_id"); }',
        'ProductCategory' => "public function products() { return \$this->hasMany(Product::class, 'category_id'); }",
        'Product' => "public function category() { return \$this->belongsTo(ProductCategory::class, 'category_id'); }\n    public function supplier() { return \$this->belongsTo(Supplier::class); }\n    public function barcodes() { return \$this->hasMany(ProductBarcode::class); }\n    public function stockLevels() { return \$this->hasMany(StockLevel::class); }",
        'ProductBarcode' => 'public function product() { return $this->belongsTo(Product::class); }',
        'StockLevel' => "public function product() { return \$this->belongsTo(Product::class); }\n    public function warehouse() { return \$this->belongsTo(Warehouse::class); }",
        'InventoryTransaction' => "public function product() { return \$this->belongsTo(Product::class); }\n    public function warehouse() { return \$this->belongsTo(Warehouse::class); }\n    public function user() { return \$this->belongsTo(User::class); }",
        'PurchaseOrder' => "public function supplier() { return \$this->belongsTo(Supplier::class); }\n    public function items() { return \$this->hasMany(PurchaseOrderItem::class); }\n    public function goodsReceipts() { return \$this->hasMany(GoodsReceipt::class); }",
        'PurchaseOrderItem' => "public function purchaseOrder() { return \$this->belongsTo(PurchaseOrder::class); }\n    public function product() { return \$this->belongsTo(Product::class); }",
        'GoodsReceipt' => "public function purchaseOrder() { return \$this->belongsTo(PurchaseOrder::class); }\n    public function items() { return \$this->hasMany(GoodsReceiptItem::class); }",
        'GoodsReceiptItem' => "public function product() { return \$this->belongsTo(Product::class); }\n    public function goodsReceipt() { return \$this->belongsTo(GoodsReceipt::class); }",
        'InventoryAdjustment' => "public function warehouse() { return \$this->belongsTo(Warehouse::class); }\n    public function items() { return \$this->hasMany(InventoryAdjustmentItem::class); }",
        'InventoryAdjustmentItem' => "public function product() { return \$this->belongsTo(Product::class); }\n    public function adjustment() { return \$this->belongsTo(InventoryAdjustment::class, 'inventory_adjustment_id'); }",
        'StockTransfer' => "public function fromWarehouse() { return \$this->belongsTo(Warehouse::class, 'from_warehouse_id'); }\n    public function toWarehouse() { return \$this->belongsTo(Warehouse::class, 'to_warehouse_id'); }\n    public function items() { return \$this->hasMany(StockTransferItem::class); }",
        'StockTransferItem' => 'public function product() { return $this->belongsTo(Product::class); }',
        'InventoryCount' => "public function warehouse() { return \$this->belongsTo(Warehouse::class); }\n    public function items() { return \$this->hasMany(InventoryCountItem::class); }",
        'InventoryCountItem' => "public function product() { return \$this->belongsTo(Product::class); }\n    public function count() { return \$this->belongsTo(InventoryCount::class, 'inventory_count_id'); }",
        'AuditLog' => 'public function user() { return $this->belongsTo(User::class); }',
        default => '',
    };

    $casts = in_array($model, ['AuditLog', 'InventoryAdjustment', 'GoodsReceipt'], true)
        ? "\n    protected \$casts = ['old_values' => 'array', 'new_values' => 'array', 'confirmed_at' => 'datetime', 'approved_at' => 'datetime'];\n"
        : '';

    track("app/Models/{$model}.php", <<<PHP
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class {$model} extends Model
{
    use HasFactory;
    {$casts}
    protected \$fillable = ['{$fillableStr}'];

    {$relations}
}
PHP);
}

$resources = array_keys($models);
$resources[] = 'User';
foreach ($resources as $model) {
    track("app/Http/Resources/{$model}Resource.php", <<<PHP
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class {$model}Resource extends JsonResource
{
    public function toArray(Request \$request): array
    {
        return parent::toArray(\$request);
    }
}
PHP);
}

track('app/Http/Resources/AuthUserResource.php', <<<'PHP'
<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthUserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'locale' => $this->locale ?? 'en',
            'theme' => $this->theme ?? 'light',
            'roles' => $this->whenLoaded('roles', fn () => $this->roles->pluck('name')),
            'permissions' => $this->getAllPermissions()->pluck('name'),
            'employee' => new EmployeeResource($this->whenLoaded('employee')),
        ];
    }
}
PHP);

file_put_contents($base . '/generated-files-part3.json', json_encode($created, JSON_PRETTY_PRINT));
echo 'Part 3: ' . count($created) . " files\n";
