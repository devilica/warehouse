<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Models\Warehouse;

class SearchService
{
    public function search(string $query): array
    {
        $like = '%' . $query . '%';

        return [
            'products' => Product::where('name', 'like', $like)->orWhere('sku', 'like', $like)->limit(10)->get(),
            'suppliers' => Supplier::where('name', 'like', $like)->limit(10)->get(),
            'employees' => Employee::where('first_name', 'like', $like)->orWhere('last_name', 'like', $like)->limit(10)->get(),
            'purchase_orders' => PurchaseOrder::where('number', 'like', $like)->limit(10)->get(),
            'warehouses' => Warehouse::where('name', 'like', $like)->limit(10)->get(),
        ];
    }
}