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