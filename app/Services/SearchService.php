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
        $like = '%'.$query.'%';
        $items = [];

        $products = Product::query()
            ->where(function ($inner) use ($like) {
                $inner->where('name', 'like', $like)->orWhere('sku', 'like', $like);
            })
            ->limit(8)
            ->get();

        foreach ($products as $product) {
            $items[] = [
                'id' => $product->id,
                'type' => 'Product',
                'label' => trim($product->name.' · '.$product->sku),
                'to' => '/products',
            ];
        }

        $warehouses = Warehouse::query()->where('name', 'like', $like)->limit(8)->get();
        foreach ($warehouses as $warehouse) {
            $items[] = [
                'id' => $warehouse->id,
                'type' => 'Warehouse',
                'label' => $warehouse->name,
                'to' => '/warehouses/'.$warehouse->id,
            ];
        }

        $orders = PurchaseOrder::query()->where('number', 'like', $like)->limit(8)->get();
        foreach ($orders as $order) {
            $items[] = [
                'id' => $order->id,
                'type' => 'Purchase order',
                'label' => $order->number,
                'to' => '/purchase-orders/'.$order->id,
            ];
        }

        $suppliers = Supplier::query()->where('company_name', 'like', $like)->limit(8)->get();
        foreach ($suppliers as $supplier) {
            $items[] = [
                'id' => $supplier->id,
                'type' => 'Supplier',
                'label' => $supplier->company_name,
                'to' => '/suppliers',
            ];
        }

        $employees = Employee::query()
            ->where(function ($inner) use ($like) {
                $inner->where('first_name', 'like', $like)->orWhere('last_name', 'like', $like);
            })
            ->limit(8)
            ->get();

        foreach ($employees as $employee) {
            $items[] = [
                'id' => $employee->id,
                'type' => 'Employee',
                'label' => trim($employee->first_name.' '.$employee->last_name),
                'to' => '/employees',
            ];
        }

        return array_slice($items, 0, 8);
    }
}
