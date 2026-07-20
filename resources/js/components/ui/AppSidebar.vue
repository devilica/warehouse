<script setup>
import { computed } from 'vue';
import { RouterLink, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { usePermission } from '@/composables/usePermission';

const route = useRoute();
const auth = useAuthStore();
const { can, canAny } = usePermission();

const navGroups = computed(() => [
    {
        title: 'Overview',
        items: [{ name: 'Dashboard', to: '/', icon: '📊' }],
    },
    {
        title: 'People',
        items: [
            { name: 'Users', to: '/users', permission: 'users.view', icon: '👤' },
            { name: 'Employees', to: '/employees', permission: 'employees.view', icon: '🧑‍💼' },
        ],
    },
    {
        title: 'Catalog',
        items: [
            { name: 'Products', to: '/products', permission: 'products.view', icon: '📦' },
            { name: 'Categories', to: '/categories', permission: 'categories.view', icon: '🏷️' },
            { name: 'Suppliers', to: '/suppliers', permission: 'suppliers.view', icon: '🏭' },
        ],
    },
    {
        title: 'Warehouses',
        items: [
            { name: 'Warehouses', to: '/warehouses', permission: 'warehouses.view', icon: '🏬' },
        ],
    },
    {
        title: 'Inventory',
        items: [
            { name: 'Stock Levels', to: '/inventory/stock-levels', permission: 'inventory.view', icon: '📈' },
            { name: 'Transactions', to: '/inventory/transactions', permission: 'inventory.view', icon: '🔄' },
            { name: 'Adjustments', to: '/inventory/adjustments', permission: 'inventory-adjustments.view', icon: '⚖️' },
            { name: 'Transfers', to: '/inventory/transfers', permission: 'stock-transfers.view', icon: '🚚' },
            { name: 'Counts', to: '/inventory/counts', permission: 'inventory-counts.view', icon: '🔢' },
        ],
    },
    {
        title: 'Procurement',
        items: [
            { name: 'Purchase Orders', to: '/purchase-orders', permission: 'purchase-orders.view', icon: '🛒' },
            { name: 'Goods Receipts', to: '/goods-receipts', permission: 'goods-receipts.view', icon: '📥' },
        ],
    },
    {
        title: 'System',
        items: [
            { name: 'Reports', to: '/reports', permission: 'reports.view', icon: '📄' },
            { name: 'Audit Log', to: '/audit', permission: 'audit.view', icon: '📋' },
            { name: 'Notifications', to: '/notifications', icon: '🔔' },
            { name: 'Profile', to: '/profile', icon: '⚙️' },
        ],
    },
].map((group) => ({
    ...group,
    items: group.items.filter((item) => !item.permission || can(item.permission)),
})).filter((group) => group.items.length));

function isActive(path) {
    if (path === '/') return route.path === '/';
    return route.path.startsWith(path);
}
</script>

<template>
    <aside class="flex h-full w-64 flex-col bg-slate-900 text-slate-300">
        <div class="border-b border-slate-800 px-5 py-5">
            <div class="text-xs font-semibold uppercase tracking-widest text-indigo-400">Company WMS</div>
            <div class="mt-1 text-lg font-bold text-white">Warehouse CMS</div>
        </div>
        <nav class="flex-1 overflow-y-auto px-3 py-4">
            <div v-for="group in navGroups" :key="group.title" class="mb-5">
                <div class="mb-2 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ group.title }}</div>
                <RouterLink
                    v-for="item in group.items"
                    :key="item.to"
                    :to="item.to"
                    class="mb-1 flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm transition"
                    :class="isActive(item.to) ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-900/30' : 'hover:bg-slate-800 hover:text-white'"
                >
                    <span>{{ item.icon }}</span>
                    <span>{{ item.name }}</span>
                </RouterLink>
            </div>
        </nav>
        <div class="border-t border-slate-800 px-4 py-4">
            <div class="truncate text-sm font-medium text-white">{{ auth.user?.name }}</div>
            <div class="truncate text-xs text-slate-500">{{ auth.user?.email }}</div>
        </div>
    </aside>
</template>
